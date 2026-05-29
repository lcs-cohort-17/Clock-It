// modules/profile/profile.js - All JavaScript Logic

// ==========================================
// STATE MANAGEMENT
// ==========================================
const ProfileState = {
    user: {
        fullName: '',
        email: '',
        employeeId: '',
        role: '',
        avatarUrl: null
    },
    crop: {
        scale: 1.0,
        minScale: 1.0,
        maxScale: 4.0,
        posX: 0,
        posY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        imageSrc: null,
        animationFrame: null
    },
    ui: {
        isEditing: false,
        activeModal: null,
        maxToasts: 3
    },

    init(bootstrapData) {
        this.user = { ...bootstrapData };
    },

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
};

// ==========================================
// UI MANAGEMENT
// ==========================================
const ProfileUI = {
    el: {},

    init() {
        const ids = [
            'displayName', 'displayEmail', 'fullNameView', 'emailView', 'employeeIdDisplay', 
            'roleDisplay', 'fullNameInput', 'emailInput', 'avatarImg', 'avatarInitials', 
            'viewButtons', 'editButtons', 'avatarUploadLabel', 'avatarUpload', 'editBtn', 
            'cancelBtn', 'profileForm', 'profileSubmitBtn', 'profileMessage', 'passwordForm', 
            'passwordSubmitBtn', 'passwordMessage', 'passwordStrength', 'currentPassword', 
            'newPassword', 'confirmPassword', 'contactAdminBtn', 'clearCacheBtn', 'confirmModal', 
            'closeModalBtn', 'cancelClearBtn', 'confirmClearBtn', 'clearSuccessMessage', 
            'cropModal', 'cropImage', 'cropArea', 'zoomSlider', 'cancelCropBtn', 'saveCropBtn', 
            'toastContainer', 'csrfToken'
        ];
        ids.forEach(id => { this.el[id] = document.getElementById(id); });
    },

    getInitials(name) {
        if (!name) return "";
        return name.trim().split(/\s+/).map(n => n[0]).join("").toUpperCase().slice(0, 2);
    },

    updateUI(userState) {
        this.el.displayName.textContent = userState.fullName;
        this.el.displayEmail.textContent = userState.email;
        this.el.fullNameView.textContent = userState.fullName;
        this.el.emailView.textContent = userState.email;
        this.el.employeeIdDisplay.textContent = userState.employeeId;
        this.el.roleDisplay.textContent = userState.role;
        this.el.fullNameInput.value = userState.fullName;
        this.el.emailInput.value = userState.email;
        
        if (userState.avatarUrl) {
            this.el.avatarImg.src = userState.avatarUrl;
            this.el.avatarImg.style.display = 'block';
            this.el.avatarInitials.style.display = 'none';
        } else {
            this.el.avatarImg.style.display = 'none';
            this.el.avatarInitials.style.display = 'block';
            this.el.avatarInitials.textContent = this.getInitials(userState.fullName);
        }
    },

    toggleEditMode(editing, userState) {
        if (!editing) this.updateUI(userState);
        this.el.fullNameView.classList.toggle('hidden', editing);
        this.el.emailView.classList.toggle('hidden', editing);
        this.el.fullNameInput.classList.toggle('hidden', !editing);
        this.el.emailInput.classList.toggle('hidden', !editing);
        this.el.viewButtons.classList.toggle('hidden', editing);
        this.el.editButtons.classList.toggle('hidden', !editing);
        this.el.avatarUploadLabel.classList.toggle('hidden', !editing);
    },

    lockBodyScroll(modalEl, state) {
        state.ui.activeModal = modalEl;
        const barWidth = window.innerWidth - document.documentElement.clientWidth;
        document.body.style.paddingRight = `${barWidth}px`;
        document.body.style.overflow = 'hidden';
        modalEl.classList.remove('hidden');
        modalEl.setAttribute('aria-hidden', 'false');
    },

    unlockBodyScroll(state) {
        if (state.ui.activeModal) {
            state.ui.activeModal.classList.add('hidden');
            state.ui.activeModal.setAttribute('aria-hidden', 'true');
        }
        state.ui.activeModal = null;
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    },

    showToast(message, type = 'info', state) {
        const container = this.el.toastContainer;
        while (container.children.length >= state.ui.maxToasts) {
            container.removeChild(container.firstChild);
        }
        const toast = document.createElement('div');
        toast.className = `profile-toast-message profile-toast-${type}`;
        toast.setAttribute('role', 'status');
        toast.textContent = message;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => { if (toast.parentNode === container) toast.remove(); }, 300);
        }, 4000);
    }
};

// ==========================================
// STORAGE (IndexedDB)
// ==========================================
const ProfileStorage = {
    db: null,
    dbName: 'ClockIO_ProfileDB',
    version: 1,

    init() {
        return new Promise((resolve) => {
            const request = indexedDB.open(this.dbName, this.version);
            
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('profile_cache')) {
                    db.createObjectStore('profile_cache', { keyPath: 'employeeId' });
                }
            };

            request.onsuccess = (e) => {
                this.db = e.target.result;
                resolve();
            };

            request.onerror = () => resolve();
        });
    },

    setProfileCache(employeeId, data) {
        if (!this.db) return;
        const transaction = this.db.transaction(['profile_cache'], 'readwrite');
        const store = transaction.objectStore('profile_cache');
        store.put({ employeeId, ...data, timestamp: Date.now() });
    },

    getProfileCache(employeeId) {
        return new Promise((resolve) => {
            if (!this.db) return resolve(null);
            const transaction = this.db.transaction(['profile_cache'], 'readonly');
            const store = transaction.objectStore('profile_cache');
            const request = store.get(employeeId);
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => resolve(null);
        });
    },

    clearCache(employeeId) {
        if (!this.db) return;
        const transaction = this.db.transaction(['profile_cache'], 'readwrite');
        const store = transaction.objectStore('profile_cache');
        store.delete(employeeId);
    }
};

// ==========================================
// CROP ENGINE
// ==========================================
const ProfileCrop = {
    clampPosition() {
        const cropArea = ProfileUI.el.cropArea;
        const img = ProfileUI.el.cropImage;
        const state = ProfileState.crop;

        const cw = cropArea.offsetWidth;
        const ch = cropArea.offsetHeight;
        const iw = img.naturalWidth * state.scale;
        const ih = img.naturalHeight * state.scale;

        const minX = cw - iw;
        const minY = ch - ih;

        state.posX = iw >= cw ? Math.min(0, Math.max(minX, state.posX)) : (cw - iw) / 2;
        state.posY = ih >= ch ? Math.min(0, Math.max(minY, state.posY)) : (ch - ih) / 2;
    },

    updateTransform() {
        ProfileUI.el.cropImage.style.transform = `translate3d(${ProfileState.crop.posX}px, ${ProfileState.crop.posY}px, 0) scale(${ProfileState.crop.scale})`;
    },

    renderFrame() {
        if (ProfileState.crop.animationFrame) return;
        ProfileState.crop.animationFrame = requestAnimationFrame(() => {
            this.updateTransform();
            ProfileState.crop.animationFrame = null;
        });
    },

    startDrag(clientX, clientY) {
        const state = ProfileState.crop;
        state.isDragging = true;
        state.startX = clientX - state.posX;
        state.startY = clientY - state.posY;
    },

    moveDrag(clientX, clientY) {
        const state = ProfileState.crop;
        if (!state.isDragging) return;
        state.posX = clientX - state.startX;
        state.posY = clientY - state.startY;
        this.clampPosition();
        this.renderFrame();
    },

    clearCropMemory() {
        if (ProfileState.crop.animationFrame) {
            cancelAnimationFrame(ProfileState.crop.animationFrame);
            ProfileState.crop.animationFrame = null;
        }
        ProfileUI.el.cropImage.src = '';
        ProfileState.crop.imageSrc = null;
        ProfileUI.el.avatarUpload.value = '';
    },

    closeCropModal() {
        ProfileUI.unlockBodyScroll(ProfileState);
        ProfileState.crop.scale = 1.0;
        ProfileState.crop.posX = 0;
        ProfileState.crop.posY = 0;
        ProfileState.crop.isDragging = false;
        this.clearCropMemory();
    }
};

// ==========================================
// EVENT HANDLERS
// ==========================================
function initEvents() {
    const { el } = ProfileUI;

    // Avatar Upload Change Event
    el.avatarUpload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 5 * 1024 * 1024) {
            ProfileUI.showToast('Image size boundary limit must be under 5MB.', 'error', ProfileState);
            el.avatarUpload.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            const testImg = new Image();
            testImg.onload = () => {
                ProfileState.crop.imageSrc = event.target.result;
                el.cropImage.src = event.target.result;
                
                ProfileUI.lockBodyScroll(el.cropModal, ProfileState);

                const fitScale = Math.max(el.cropArea.offsetWidth / testImg.naturalWidth, el.cropArea.offsetHeight / testImg.naturalHeight);
                ProfileState.crop.scale = fitScale;
                ProfileState.crop.minScale = fitScale;
                ProfileState.crop.maxScale = fitScale * 4;

                el.zoomSlider.min = fitScale;
                el.zoomSlider.max = fitScale * 4;
                el.zoomSlider.value = fitScale;

                ProfileState.crop.posX = (el.cropArea.offsetWidth - (testImg.naturalWidth * fitScale)) / 2;
                ProfileState.crop.posY = (el.cropArea.offsetHeight - (testImg.naturalHeight * fitScale)) / 2;

                ProfileCrop.clampPosition();
                ProfileCrop.updateTransform();
            };
            testImg.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Zoom Range Control Listener
    el.zoomSlider.addEventListener('input', (e) => {
        const prevScale = ProfileState.crop.scale;
        ProfileState.crop.scale = parseFloat(e.target.value);

        const cX = el.cropArea.offsetWidth / 2;
        const cY = el.cropArea.offsetHeight / 2;

        ProfileState.crop.posX = cX - ((cX - ProfileState.crop.posX) * (ProfileState.crop.scale / prevScale));
        ProfileState.crop.posY = cY - ((cY - ProfileState.crop.posY) * (ProfileState.crop.scale / prevScale));

        ProfileCrop.clampPosition();
        ProfileCrop.renderFrame();
    });

    // Workspace Drag pointer listeners
    el.cropArea.addEventListener('mousedown', (e) => { if (e.button === 0) ProfileCrop.startDrag(e.clientX, e.clientY); });
    window.addEventListener('mousemove', (e) => ProfileCrop.moveDrag(e.clientX, e.clientY));
    window.addEventListener('mouseup', () => { ProfileState.crop.isDragging = false; });

    // Touch Interaction
    el.cropArea.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) ProfileCrop.startDrag(e.touches[0].clientX, e.touches[0].clientY);
    }, { passive: true });
    window.addEventListener('touchmove', (e) => {
        if (!ProfileState.crop.isDragging) return;
        if (e.cancelable) e.preventDefault();
        ProfileCrop.moveDrag(e.touches[0].clientX, e.touches[0].clientY);
    }, { passive: false });
    window.addEventListener('touchend', () => { ProfileState.crop.isDragging = false; });
    window.addEventListener('touchcancel', () => { ProfileState.crop.isDragging = false; });

    // Save Crop
    el.saveCropBtn.addEventListener('click', () => {
        el.saveCropBtn.disabled = true;
        el.saveCropBtn.textContent = 'Uploading...';

        requestAnimationFrame(() => {
            try {
                const canvas = document.createElement('canvas');
                const OUTPUT_SIZE = 512;
                canvas.width = OUTPUT_SIZE;
                canvas.height = OUTPUT_SIZE;
                const ctx = canvas.getContext('2d');
                
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                const scaleFactor = ProfileUI.el.cropImage.naturalWidth / (ProfileUI.el.cropImage.naturalWidth * ProfileState.crop.scale);
                const srcX = -ProfileState.crop.posX * scaleFactor;
                const srcY = -ProfileState.crop.posY * scaleFactor;
                const cropSize = Math.min(el.cropArea.offsetWidth, el.cropArea.offsetHeight);
                const srcW = cropSize * scaleFactor;
                const srcH = cropSize * scaleFactor;

                ctx.drawImage(el.cropImage, srcX, srcY, srcW, srcH, 0, 0, OUTPUT_SIZE, OUTPUT_SIZE);

                canvas.toBlob((blob) => {
                    if (!blob) throw new Error("Canvas generation aborted.");

                    const formData = new FormData();
                    formData.append('avatar', blob, 'avatar.webp');
                    formData.append('csrf_token', el.csrfToken.value);

                    fetch('/profile/avatar/', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            ProfileState.user.avatarUrl = data.url;
                            ProfileUI.updateUI(ProfileState.user);
                            ProfileStorage.setProfileCache(ProfileState.user.employeeId, ProfileState.user);
                            ProfileUI.showToast("Profile image uploaded.", "success", ProfileState);
                            ProfileCrop.closeCropModal();
                        } else {
                            throw new Error(data.message || "Upload failed.");
                        }
                    })
                    .catch(err => {
                        ProfileUI.showToast(err.message, "error", ProfileState);
                    })
                    .finally(() => {
                        el.saveCropBtn.disabled = false;
                        el.saveCropBtn.textContent = 'Save image';
                    });
                }, 'image/webp', 0.85);

            } catch (err) {
                ProfileUI.showToast("Failed to process image.", "error", ProfileState);
                el.saveCropBtn.disabled = false;
                el.saveCropBtn.textContent = 'Save image';
            }
        });
    });

    // Cancel Crop
    el.cancelCropBtn.addEventListener('click', () => ProfileCrop.closeCropModal());

    // Edit/Cancel Profile
    el.editBtn.addEventListener('click', () => ProfileUI.toggleEditMode(true, ProfileState.user));
    el.cancelBtn.addEventListener('click', () => ProfileUI.toggleEditMode(false, ProfileState.user));

    // Profile Form Submit
    el.profileForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const name = el.fullNameInput.value.replace(/\s+/g, ' ').trim();
        const email = el.emailInput.value.trim();

        if (name.length < 3 || name.length > 60) {
            ProfileUI.showToast("Name must be 3-60 characters.", "error", ProfileState);
            return;
        }
        if (!ProfileState.validateEmail(email)) {
            ProfileUI.showToast("Invalid email address.", "error", ProfileState);
            return;
        }

        el.profileSubmitBtn.disabled = true;
        ProfileState.user.fullName = name;
        ProfileState.user.email = email;

        ProfileStorage.setProfileCache(ProfileState.user.employeeId, ProfileState.user);
        
        setTimeout(() => {
            el.profileSubmitBtn.disabled = false;
            ProfileUI.toggleEditMode(false, ProfileState.user);
            ProfileUI.showToast("Profile saved successfully.", "success", ProfileState);
        }, 1000);
    });

    // Password Strength
    el.newPassword.addEventListener('input', (e) => {
        const val = e.target.value;
        if (!val) { el.passwordStrength.classList.add('hidden'); return; }
        el.passwordStrength.classList.remove('hidden');
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSpecial = /[^A-Za-z0-9]/.test(val);
        const hasMinLen = val.length >= 8;
        const score = [hasUpper, hasLower, hasNumber, hasSpecial, hasMinLen].filter(Boolean).length;
        
        if (score <= 2) { el.passwordStrength.textContent = "Weak"; el.passwordStrength.style.color = "#dc2626"; }
        else if (score <= 4) { el.passwordStrength.textContent = "Medium"; el.passwordStrength.style.color = "#d97706"; }
        else { el.passwordStrength.textContent = "Strong"; el.passwordStrength.style.color = "#16a34a"; }
    });

    // Password Form Submit
    el.passwordForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if(!el.currentPassword.value || !el.newPassword.value || el.newPassword.value !== el.confirmPassword.value) {
            ProfileUI.showToast("Passwords do not match or are incomplete.", "error", ProfileState);
            return;
        }

        el.passwordSubmitBtn.disabled = true;
        
        fetch('/profile/password/', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                current: el.currentPassword.value,
                new: el.newPassword.value,
                csrf_token: el.csrfToken.value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                el.currentPassword.value = '';
                el.newPassword.value = '';
                el.confirmPassword.value = '';
                ProfileUI.showToast("Password updated successfully.", "success", ProfileState);
            } else {
                ProfileUI.showToast(data.message, "error", ProfileState);
            }
        })
        .catch(() => ProfileUI.showToast("Network error updating password.", "error", ProfileState))
        .finally(() => { el.passwordSubmitBtn.disabled = false; });
    });

    // Clear Cache Modal
    el.clearCacheBtn.addEventListener('click', () => ProfileUI.lockBodyScroll(el.confirmModal, ProfileState));
    el.closeModalBtn.addEventListener('click', () => ProfileUI.unlockBodyScroll(ProfileState));
    el.cancelClearBtn.addEventListener('click', () => ProfileUI.unlockBodyScroll(ProfileState));
    
    el.confirmClearBtn.addEventListener('click', () => {
        ProfileStorage.clearCache(ProfileState.user.employeeId);
        ProfileState.user.avatarUrl = null;
        ProfileUI.unlockBodyScroll(ProfileState);
        ProfileUI.toggleEditMode(false, ProfileState.user);
        ProfileUI.showToast("Cache cleared successfully.", "success", ProfileState);
    });

    // Contact Admin
    el.contactAdminBtn.addEventListener('click', () => {
        window.location.href = 'mailto:admin@clockit.com?subject=Clock-It Support Request';
    });

    // Escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && ProfileState.ui.activeModal) {
            if (ProfileState.ui.activeModal === el.cropModal) ProfileCrop.closeCropModal();
            else ProfileUI.unlockBodyScroll(ProfileState);
        }
    });

    // Modal click outside
    el.cropModal.addEventListener('click', (e) => { if (e.target === el.cropModal) ProfileCrop.closeCropModal(); });
    el.confirmModal.addEventListener('click', (e) => { if (e.target === el.confirmModal) ProfileUI.unlockBodyScroll(ProfileState); });

    const passwordIcons = {
        show: `
            <svg class="profile-password-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"></path>
                <circle cx="12" cy="12" r="2.75"></circle>
            </svg>
        `,
        hide: `
            <svg class="profile-password-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M3 3l18 18"></path>
                <path d="M9.1 5.35A9.7 9.7 0 0 1 12 4.95c6.25 0 9.75 6.25 9.75 6.25a17.5 17.5 0 0 1-3.18 3.85"></path>
                <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                <path d="M6.35 6.9A17.14 17.14 0 0 0 2.25 12S5.75 18.25 12 18.25a9.78 9.78 0 0 0 4.04-.86"></path>
            </svg>
        `
    };

    function setPasswordToggleIcon(button, isVisible) {
        button.innerHTML = isVisible ? passwordIcons.hide : passwordIcons.show;
        button.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
    }

    // Password Toggle
    document.querySelectorAll('.profile-password-toggle').forEach(btn => {
        setPasswordToggleIcon(btn, false);

        btn.addEventListener('click', function() {
            const target = document.getElementById(this.getAttribute('data-target'));
            if (target) {
                const isVisible = target.type === 'password';
                target.type = isVisible ? 'text' : 'password';
                setPasswordToggleIcon(this, isVisible);
            }
        });
    });
}

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    ProfileState.init(window.ProfileBootstrap);
    ProfileUI.init();
    ProfileStorage.init().then(() => {
        ProfileStorage.getProfileCache(ProfileState.user.employeeId).then(cached => {
            if (cached) {
                ProfileState.user.fullName = cached.fullName || ProfileState.user.fullName;
                ProfileState.user.email = cached.email || ProfileState.user.email;
                if (cached.avatarUrl && cached.avatarUrl.startsWith('/')) {
                    ProfileState.user.avatarUrl = cached.avatarUrl;
                }
            }
            ProfileUI.updateUI(ProfileState.user);
            initEvents();
        });
    });
});
