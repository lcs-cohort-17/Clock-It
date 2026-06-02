// OneTimeQRModal.jsx
import React, { useState, useEffect, useRef, useCallback } from 'react';
import QRCode from 'qrcode';
import './OneTimeQRModal.css';

const EXPIRY_SECONDS = 45;

const OneTimeQRModal = () => {
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [qrType, setQrType] = useState(null); // 'clock_in' or 'clock_out'
    const [currentToken, setCurrentToken] = useState(null);
    const [expiryTimestamp, setExpiryTimestamp] = useState(null);
    const [isUsed, setIsUsed] = useState(false);
    const [qrStatus, setQrStatus] = useState('');
    const [feedbackMsg, setFeedbackMsg] = useState('');
    const canvasRef = useRef(null);
    let expiryInterval = useRef(null);

    const generateUniqueToken = () => {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'token-' + Date.now() + '-' + Math.random().toString(36).substring(2);
    };

    const clearExpiryTimer = () => {
        if (expiryInterval.current) clearInterval(expiryInterval.current);
    };

    const updateUI = useCallback(() => {
        const now = Date.now();
        const isExpired = expiryTimestamp ? now >= expiryTimestamp : false;

    if (isUsed) {
        setQrStatus('used');
        setFeedbackMsg('QR already used – one‑time consumption done.');
    } else if (isExpired) {
        setQrStatus('expired');
        setFeedbackMsg('QR expired. Click Refresh to generate a new one.');
    } else if (currentToken && !isUsed && !isExpired) {
        setQrStatus('valid');
        setFeedbackMsg(`Valid ${qrType === 'clock_in' ? 'Clock In' : 'Clock Out'} QR – ready to scan.`);
    } else {
        setQrStatus('error');
        setFeedbackMsg('No active QR – generate from dropdown.');
    }
    }, [currentToken, expiryTimestamp, isUsed, qrType]);

    const startCountdown = useCallback(() => {
        clearExpiryTimer();
        expiryInterval.current = setInterval(() => {
            if (!isModalOpen) return;
            const now = Date.now();
            if (expiryTimestamp && now >= expiryTimestamp && !isUsed) {
                setQrStatus('expired');
                setFeedbackMsg('QR expired – please refresh.');
                clearExpiryTimer();
                }
            updateUI();
        }, 1000);
    }, [expiryTimestamp, isModalOpen, isUsed, updateUI]);

    const generateFreshQR = async (type) => {
        try {
            const newToken = generateUniqueToken();
            const newExpiry = Date.now() + EXPIRY_SECONDS * 1000;
            setCurrentToken(newToken);
            setExpiryTimestamp(newExpiry);
            setIsUsed(false);
            setQrType(type);
            setQrStatus('valid');

        const payload = JSON.stringify({
            action: type,
            token: newToken,
            expiresAt: newExpiry,
            version: '1',
        });

        if (canvasRef.current) {
            await QRCode.toCanvas(canvasRef.current, payload, {
                width: 240,
                margin: 1,
                color: { dark: '#0f172a', light: '#FFFFFF' },
            });
        }
        updateUI();
        startCountdown();
    } catch (err) {
        console.error(err);
        setQrStatus('error');
        setFeedbackMsg('QR generation failed. Please try again.');
        }
    };

    const refreshQR = async () => {
        if (!qrType) {
            setFeedbackMsg('Select Clock In/Out from dropdown first.');
            return;
        }
        await generateFreshQR(qrType);
    };

    const simulateScan = () => {
        if (isUsed) {
            setFeedbackMsg('QR already used – refresh for a new code.');
            return;
        }
        if (!expiryTimestamp || Date.now() >= expiryTimestamp) {
            setFeedbackMsg('QR expired – refresh before scanning.');
            return;
        }
        if (!currentToken) return;
            setIsUsed(true);
            setQrStatus('used');
            setFeedbackMsg(`${qrType === 'clock_in' ? 'Clock In' : 'Clock Out'} recorded! QR is now invalid.`);
            clearExpiryTimer();
            updateUI();
        };

    const openModalWithType = async (type) => {
        setIsDropdownOpen(false);
        setIsModalOpen(true);
        await generateFreshQR(type);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        clearExpiryTimer();
        setCurrentToken(null);
        setIsUsed(false);
        setExpiryTimestamp(null);
        setQrType(null);
        setQrStatus('');
        setFeedbackMsg('');
            if (canvasRef.current) {
            const ctx = canvasRef.current.getContext('2d');
            ctx.clearRect(0, 0, canvasRef.current.width, canvasRef.current.height);
        }
    };

    useEffect(() => {
        if (!isModalOpen) {
            clearExpiryTimer();
        }
        return () => clearExpiryTimer();
    }, [isModalOpen]);

    return (
        <div className="app-container">
            <h1><i className="bi bi-clock-history"></i> ClockFlow</h1>
            <div className="sub">One‑time QR | secure attendance</div>

        <div className="dropdown">
            <button
            className="dropdown-btn"
            onClick={() => setIsDropdownOpen(!isDropdownOpen)}
            aria-expanded={isDropdownOpen}
            >
                <span><i className="bi bi-card-list"></i> Clock Actions</span>
                <i className={`dropdown-icon bi ${isDropdownOpen ? 'bi-chevron-up' : 'bi-chevron-down'}`}></i>
            </button>
        {isDropdownOpen && (
            <div className="dropdown-menu">
                <div className="dropdown-item" onClick={() => openModalWithType('clock_in')}>
                    <i className="bi bi-box-arrow-in-right"></i> Clock In QR
                </div>
                <div className="dropdown-item" onClick={() => openModalWithType('clock_out')}>
                    <i className="bi bi-box-arrow-right"></i> Clock Out QR
                </div>
            </div>
            )}
        </div>

        {isModalOpen && (
            <div className="modal-overlay active" onClick={(e) => e.target === e.currentTarget && closeModal()}>
                <div className="modal-container">
                    <div className="modal-header">
                    <h2><i className="bi bi-qr-code"></i> {qrType === 'clock_in' ? 'Clock In QR' : 'Clock Out QR'}</h2>
                    <button className="close-modal" onClick={closeModal}><i className="bi bi-x-lg"></i></button>
                    </div>
                    <div className="modal-body">
                        <div className="qr-canvas-wrapper">
                            <canvas ref={canvasRef} width="240" height="240"></canvas>
                        </div>
                        <div className={`qr-status status-${qrStatus}`}>
                            {qrStatus === 'valid' && <><i className="bi bi-check-circle-fill"></i> Ready – valid & unused</>}
                            {qrStatus === 'expired' && <><i className="bi bi-hourglass-split"></i> QR expired – refresh</>}
                            {qrStatus === 'used' && <><i className="bi bi-lock-fill"></i> Already used (one‑time)</>}
                            {qrStatus === 'error' && <><i className="bi bi-exclamation-triangle-fill"></i> Error – try refresh</>}
                        </div>
                        <div className="timer-box">
                            {!isUsed && expiryTimestamp && Date.now() < expiryTimestamp ? (
                                <><i className="bi bi-stopwatch"></i> Expires in {Math.max(0, Math.floor((expiryTimestamp - Date.now()) / 1000))}s</>
                            ) : isUsed ? (
                                <><i className="bi bi-check2-circle"></i> Already scanned</>
                            ) : (
                                <><i className="bi bi-hourglass-top"></i> No active QR</>
                            )}
                        </div>
                        <div className="action-buttons">
                            <button className="btn-refresh" onClick={refreshQR}><i className="bi bi-arrow-repeat"></i> Refresh QR</button>
                            <button className="btn-scan-sim" onClick={simulateScan} disabled={qrStatus !== 'valid'}>
                                <i className="bi bi-upc-scan"></i> Simulate Scan
                            </button>
                            <button className="btn-secondary" onClick={closeModal}><i className="bi bi-x-circle"></i> Close</button>
                        </div>
                        <div className="feedback-message"><i className="bi bi-info-circle"></i> {feedbackMsg}</div>
                    </div>
                </div>
            </div>
        )}
    </div>
  );
};

export default OneTimeQRModal;