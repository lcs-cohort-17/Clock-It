(function () {
  const seed = window.ClockItSeed || {};
  const prefix = 'clockit.';

  const clone = (value) => JSON.parse(JSON.stringify(value || null));
  const pad = (value) => String(value).padStart(2, '0');
  const today = () => seed.today || new Date().toISOString().slice(0, 10);

  function route(path) {
    const clean = `/${String(path || '/').replace(/^\/+/, '')}`;
    return `${seed.baseUrl || ''}/index.php${clean === '/' ? '' : clean}`;
  }

  function read(key, fallback) {
    const storageKey = prefix + key;
    try {
      const stored = localStorage.getItem(storageKey);
      if (stored) {
        return JSON.parse(stored);
      }
      const seeded = clone(fallback);
      localStorage.setItem(storageKey, JSON.stringify(seeded));
      return seeded;
    } catch (error) {
      return clone(fallback);
    }
  }

  function write(key, value) {
    try {
      localStorage.setItem(prefix + key, JSON.stringify(value));
    } catch (error) {
      return false;
    }
    return true;
  }

  function users() {
    return read('users', seed.users || []);
  }

  function records() {
    const items = read('records', seed.attendanceRecords || []);
    return enforceConsistency(items);
  }

  function qrTokens() {
    return read('qrTokens', seed.qrTokens || {});
  }

  function settings() {
    return read('settings', seed.settings || {});
  }

  function calendarSeed() {
    return seed.calendar || { holidays: [], leave: [], schedule: [] };
  }

  function saveUsers(items) {
    return write('users', items);
  }

  function saveRecords(items) {
    const consistent = enforceConsistency(items);
    return write('records', consistent);
  }

  function saveQrTokens(items) {
    return write('qrTokens', items);
  }

  function saveSettings(items) {
    return write('settings', items);
  }

  function dateKey(value) {
    if (!value) {
      return '';
    }
    return String(value).slice(0, 10);
  }

  function formatDate(value) {
    if (!value) {
      return 'Not set';
    }
    return new Intl.DateTimeFormat('en-ZA', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    }).format(new Date(value));
  }

  function formatTime(value) {
    if (!value) {
      return 'Pending';
    }
    return new Intl.DateTimeFormat('en-ZA', {
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(value));
  }

  function formatDateTime(value) {
    if (!value) {
      return 'Not recorded';
    }
    return new Intl.DateTimeFormat('en-ZA', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(value));
  }

  function sortedRecords(items) {
    return [...items].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
  }

  function isClockIn(record) {
    return String(record.type || '').toLowerCase().includes('clock in');
  }

  function isClockOut(record) {
    return String(record.type || '').toLowerCase().includes('clock out');
  }

  function employeeById(employeeId, userList = users()) {
    return userList.find((user) => user.employeeId === employeeId) || null;
  }

  function latestRecordFor(employeeId, items = records()) {
    return sortedRecords(items.filter((record) => record.employeeId === employeeId))[0] || null;
  }

  function currentStatus(employeeId, items = records()) {
    const latest = latestRecordFor(employeeId, items);
    if (!latest || isClockOut(latest)) {
      return 'Clocked Out';
    }
    return 'Clocked In';
  }

  function enforceConsistency(items) {
    const grouped = {};
    const next = clone(items || []);

    next.forEach((record) => {
      grouped[record.employeeId] = grouped[record.employeeId] || [];
      grouped[record.employeeId].push(record);
    });

    Object.keys(grouped).forEach((employeeId) => {
      const employeeRecords = grouped[employeeId].sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
      let openClockIn = null;

      employeeRecords.forEach((record) => {
        if (!record.auditTrail) {
          record.auditTrail = [];
        }

        if (isClockIn(record)) {
          openClockIn = record;
          if (record.status === 'Clock Out Pending') {
            record.status = 'Clock Out Pending';
          }
        }

        if (isClockOut(record)) {
          if (openClockIn && openClockIn.status === 'Clock Out Pending') {
            openClockIn.status = 'Verified';
            openClockIn.updatedAt = record.updatedAt || record.timestamp;
          }
          openClockIn = null;
        }
      });

      employeeRecords.forEach((record) => {
        if (isClockIn(record) && record !== openClockIn && record.status === 'Clock Out Pending') {
          record.status = 'Verified';
        }
      });

      if (openClockIn && isClockIn(openClockIn)) {
        openClockIn.status = 'Clock Out Pending';
      }
    });

    return next;
  }

  function todaysRecords(items = records()) {
    const key = today();
    return items.filter((record) => dateKey(record.timestamp) === key);
  }

  function uniqueCount(items, selector) {
    return new Set(items.map(selector)).size;
  }

  function dashboardMetrics(itemList = records(), userList = users()) {
    const todayItems = todaysRecords(itemList);
    const activeStaff = userList.filter((user) => user.accountRole === 'staff' && user.status === 'Active');
    const onsite = activeStaff.filter((user) => currentStatus(user.employeeId, itemList) === 'Clocked In');
    const pending = todayItems.filter((record) => record.status === 'Pending Review' || record.syncStatus === 'Pending');

    return {
      currentlyOnsite: onsite.length,
      totalClockedInToday: uniqueCount(todayItems.filter(isClockIn), (record) => record.employeeId),
      pendingSyncReview: pending.length,
      totalEventsToday: todayItems.length,
    };
  }

  function workSummary(employeeId, date = today(), itemList = records()) {
    const items = itemList
      .filter((record) => record.employeeId === employeeId && dateKey(record.timestamp) === date)
      .sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
    const clockIn = items.find(isClockIn) || null;
    const clockOut = [...items].reverse().find(isClockOut) || null;
    let hours = 0;

    if (clockIn) {
      const end = clockOut ? new Date(clockOut.timestamp) : new Date(seed.now || Date.now());
      const start = new Date(clockIn.timestamp);
      hours = Math.max(0, (end - start) / 36e5);
    }

    return {
      clockIn,
      clockOut,
      totalHours: hours,
      status: currentStatus(employeeId, itemList),
      records: items,
    };
  }

  function statusClass(status) {
    const key = String(status || '').toLowerCase().replace(/\s+/g, '-');
    const classes = {
      verified: 'status-verified',
      active: 'status-verified',
      synced: 'status-verified',
      'pending-review': 'status-pending',
      pending: 'status-pending',
      'clock-out-pending': 'status-clock-out-pending',
      flagged: 'status-flagged',
      disabled: 'status-disabled',
      'clocked-in': 'status-clocked-in',
      'clocked-out': 'status-clocked-out',
    };
    return `status-pill ${classes[key] || ''}`;
  }

  function deviceType() {
    const ua = navigator.userAgent.toLowerCase();
    if (/ipad|tablet/.test(ua)) {
      return 'Tablet';
    }
    if (/mobi|android|iphone/.test(ua)) {
      return 'Mobile';
    }
    return 'Desktop';
  }

  function initials(name) {
    return String(name || 'HR')
      .split(/\s+/)
      .filter(Boolean)
      .slice(0, 2)
      .map((part) => part[0].toUpperCase())
      .join('');
  }

  function escapeCsv(value) {
    const text = String(value ?? '');
    if (/[",\n]/.test(text)) {
      return `"${text.replace(/"/g, '""')}"`;
    }
    return text;
  }

  function downloadCsv(filename, rows) {
    const csv = rows.map((row) => row.map(escapeCsv).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(link.href);
  }

  function showModal(id) {
    const el = document.getElementById(id);
    if (!el) {
      return;
    }

    if (window.bootstrap?.Modal) {
      try {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
        return;
      } catch (error) {
        console.warn('Bootstrap modal failed, using Clock-It fallback modal.', error);
      }
    }

    el.classList.add('show');
    el.removeAttribute('aria-hidden');
    el.setAttribute('aria-modal', 'true');
    el.setAttribute('role', 'dialog');
    el.style.display = 'block';
    document.body.classList.add('modal-open');

    if (!document.querySelector(`.modal-backdrop[data-fallback-for="${id}"]`)) {
      const backdrop = document.createElement('div');
      backdrop.className = 'modal-backdrop fade show';
      backdrop.dataset.fallbackFor = id;
      backdrop.addEventListener('click', () => hideModal(id));
      document.body.appendChild(backdrop);
    }
  }

  function hideModal(id) {
    const el = document.getElementById(id);
    if (!el) {
      return;
    }

    if (window.bootstrap?.Modal) {
      try {
        window.bootstrap.Modal.getOrCreateInstance(el).hide();
        return;
      } catch (error) {
        console.warn('Bootstrap modal hide failed, using Clock-It fallback modal.', error);
      }
    }

    el.classList.remove('show');
    el.setAttribute('aria-hidden', 'true');
    el.removeAttribute('aria-modal');
    el.removeAttribute('role');
    el.style.display = 'none';
    document.querySelectorAll(`.modal-backdrop[data-fallback-for="${id}"]`).forEach((backdrop) => backdrop.remove());

    if (!document.querySelector('.modal.show')) {
      document.body.classList.remove('modal-open');
    }
  }

  function createAttendanceRecord(typeKey, currentUser, token) {
    const stamp = new Date().toISOString();
    const isIn = typeKey === 'clockIn';
    return {
      id: `ATT-${Date.now()}-${Math.floor(Math.random() * 90 + 10)}`,
      employeeId: currentUser.employeeId,
      employeeName: currentUser.name,
      department: currentUser.department,
      role: currentUser.role || 'Staff',
      type: isIn ? 'Global Clock In' : 'Global Clock Out',
      timestamp: stamp,
      device: deviceType(),
      qrUsed: token,
      status: isIn ? 'Clock Out Pending' : 'Verified',
      syncStatus: 'Synced',
      createdAt: stamp,
      updatedAt: stamp,
      notes: isIn ? 'Frontend scan accepted. Clock out is pending.' : 'Frontend scan accepted. Shift closed.',
      auditTrail: [
        {
          at: stamp,
          actor: 'Frontend demo',
          event: isIn ? 'Clock in created from QR scan.' : 'Clock out created from QR scan.',
        },
      ],
    };
  }

  function departmentPresence(itemList = records(), userList = users()) {
    const departments = {};
    userList
      .filter((user) => user.accountRole === 'staff' && user.status === 'Active')
      .forEach((user) => {
        departments[user.department] = departments[user.department] || { department: user.department, total: 0, onsite: 0 };
        departments[user.department].total += 1;
        if (currentStatus(user.employeeId, itemList) === 'Clocked In') {
          departments[user.department].onsite += 1;
        }
      });

    return Object.values(departments).map((dept) => ({
      ...dept,
      percent: dept.total ? Math.round((dept.onsite / dept.total) * 100) : 0,
    }));
  }

  function calendarMarkersFor(date, scope, itemList = records()) {
    const currentUser = seed.currentUser || {};
    const calendar = calendarSeed();
    const scopeRecords = itemList.filter((record) => {
      if (dateKey(record.timestamp) !== date) {
        return false;
      }
      return scope === 'staff' ? record.employeeId === currentUser.employeeId : true;
    });

    const markers = [];
    if (scopeRecords.length) {
      markers.push({ type: 'attendance', label: `${scopeRecords.length} attendance` });
    }

    calendar.leave
      .filter((leave) => leave.date === date && (scope !== 'staff' || leave.employeeId === currentUser.employeeId))
      .forEach((leave) => markers.push({ type: 'leave', label: leave.type }));

    calendar.holidays
      .filter((holiday) => holiday.date === date)
      .forEach((holiday) => markers.push({ type: 'holiday', label: holiday.name }));

    calendar.schedule
      .filter((schedule) => schedule.date === date && (scope !== 'staff' || schedule.employeeId === currentUser.employeeId))
      .forEach((schedule) => markers.push({ type: 'schedule', label: schedule.shift }));

    return markers;
  }

  function createQrMatrix(text) {
    const version = 4;
    const size = 21 + 4 * (version - 1);
    const dataCodewords = 80;
    const eccCodewords = 20;
    const mask = 0;
    const modules = Array.from({ length: size }, () => Array(size).fill(false));
    const isFunction = Array.from({ length: size }, () => Array(size).fill(false));

    function setModule(x, y, dark, functional = false) {
      if (x < 0 || y < 0 || x >= size || y >= size) {
        return;
      }
      modules[y][x] = dark;
      if (functional) {
        isFunction[y][x] = true;
      }
    }

    function drawFinder(x, y) {
      for (let dy = -1; dy <= 7; dy += 1) {
        for (let dx = -1; dx <= 7; dx += 1) {
          const xx = x + dx;
          const yy = y + dy;
          const inBounds = xx >= 0 && yy >= 0 && xx < size && yy < size;
          if (!inBounds) {
            continue;
          }
          const dark =
            (dx >= 0 && dx <= 6 && (dy === 0 || dy === 6)) ||
            (dy >= 0 && dy <= 6 && (dx === 0 || dx === 6)) ||
            (dx >= 2 && dx <= 4 && dy >= 2 && dy <= 4);
          setModule(xx, yy, dark, true);
        }
      }
    }

    function drawAlignment(cx, cy) {
      for (let dy = -2; dy <= 2; dy += 1) {
        for (let dx = -2; dx <= 2; dx += 1) {
          const distance = Math.max(Math.abs(dx), Math.abs(dy));
          setModule(cx + dx, cy + dy, distance !== 1, true);
        }
      }
    }

    function reserveFormat() {
      for (let i = 0; i < 9; i += 1) {
        setModule(8, i, false, true);
        setModule(i, 8, false, true);
      }
      for (let i = size - 8; i < size; i += 1) {
        setModule(8, i, false, true);
        setModule(i, 8, false, true);
      }
    }

    function gfMultiply(x, y) {
      let z = 0;
      for (let i = 7; i >= 0; i -= 1) {
        z = (z << 1) ^ (((z >>> 7) & 1) * 0x11d);
        if (((y >>> i) & 1) !== 0) {
          z ^= x;
        }
      }
      return z & 0xff;
    }

    function reedSolomonDivisor(degree) {
      const result = Array(degree).fill(0);
      result[degree - 1] = 1;
      let root = 1;
      for (let i = 0; i < degree; i += 1) {
        for (let j = 0; j < result.length; j += 1) {
          result[j] = gfMultiply(result[j], root);
          if (j + 1 < result.length) {
            result[j] ^= result[j + 1];
          }
        }
        root = gfMultiply(root, 0x02);
      }
      return result;
    }

    function reedSolomonRemainder(data, divisor) {
      const result = Array(divisor.length).fill(0);
      data.forEach((value) => {
        const factor = value ^ result.shift();
        result.push(0);
        divisor.forEach((coefficient, index) => {
          result[index] ^= gfMultiply(coefficient, factor);
        });
      });
      return result;
    }

    function appendBits(bits, value, length) {
      for (let i = length - 1; i >= 0; i -= 1) {
        bits.push((value >>> i) & 1);
      }
    }

    function bytesFromText(value) {
      return Array.from(new TextEncoder().encode(value));
    }

    function makeCodewords(value) {
      const bytes = bytesFromText(value);
      if (bytes.length > 62) {
        throw new Error('QR payload is too long for the built-in Clock-It QR version.');
      }

      const bits = [];
      appendBits(bits, 0x4, 4);
      appendBits(bits, bytes.length, 8);
      bytes.forEach((byte) => appendBits(bits, byte, 8));

      const capacityBits = dataCodewords * 8;
      appendBits(bits, 0, Math.min(4, capacityBits - bits.length));
      while (bits.length % 8 !== 0) {
        bits.push(0);
      }

      const data = [];
      for (let i = 0; i < bits.length; i += 8) {
        data.push(bits.slice(i, i + 8).reduce((sum, bit) => (sum << 1) | bit, 0));
      }

      for (let pad = 0xec; data.length < dataCodewords; pad ^= 0xec ^ 0x11) {
        data.push(pad);
      }

      return [...data, ...reedSolomonRemainder(data, reedSolomonDivisor(eccCodewords))];
    }

    function maskBit(x, y) {
      return (x + y) % 2 === 0;
    }

    function drawFormatBits() {
      const eclBits = 1;
      const data = (eclBits << 3) | mask;
      let bits = data << 10;
      const generator = 0x537;
      for (let i = 14; i >= 10; i -= 1) {
        if (((bits >>> i) & 1) !== 0) {
          bits ^= generator << (i - 10);
        }
      }
      bits = ((data << 10) | bits) ^ 0x5412;

      for (let i = 0; i <= 5; i += 1) {
        setModule(8, i, ((bits >>> i) & 1) !== 0, true);
      }
      setModule(8, 7, ((bits >>> 6) & 1) !== 0, true);
      setModule(8, 8, ((bits >>> 7) & 1) !== 0, true);
      setModule(7, 8, ((bits >>> 8) & 1) !== 0, true);
      for (let i = 9; i < 15; i += 1) {
        setModule(14 - i, 8, ((bits >>> i) & 1) !== 0, true);
      }

      for (let i = 0; i < 8; i += 1) {
        setModule(size - 1 - i, 8, ((bits >>> i) & 1) !== 0, true);
      }
      for (let i = 8; i < 15; i += 1) {
        setModule(8, size - 15 + i, ((bits >>> i) & 1) !== 0, true);
      }
      setModule(8, size - 8, true, true);
    }

    drawFinder(0, 0);
    drawFinder(size - 7, 0);
    drawFinder(0, size - 7);
    drawAlignment(26, 26);

    for (let i = 8; i < size - 8; i += 1) {
      setModule(6, i, i % 2 === 0, true);
      setModule(i, 6, i % 2 === 0, true);
    }
    reserveFormat();

    const codewords = makeCodewords(String(text || ''));
    const bits = [];
    codewords.forEach((codeword) => appendBits(bits, codeword, 8));

    let bitIndex = 0;
    let upward = true;
    for (let right = size - 1; right >= 1; right -= 2) {
      if (right === 6) {
        right -= 1;
      }
      for (let vert = 0; vert < size; vert += 1) {
        const y = upward ? size - 1 - vert : vert;
        for (let x = right; x >= right - 1; x -= 1) {
          if (isFunction[y][x]) {
            continue;
          }
          const rawBit = bitIndex < bits.length ? bits[bitIndex] !== 0 : false;
          setModule(x, y, rawBit !== maskBit(x, y), false);
          bitIndex += 1;
        }
      }
      upward = !upward;
    }

    drawFormatBits();
    return modules;
  }

  function drawQr(canvas, token, label) {
    if (!canvas) {
      return;
    }

    const payload = String(token || 'REVOKED');
    const modules = createQrMatrix(payload);
    const quietZone = 4;
    const moduleCount = modules.length;
    const scale = 8;
    const labelHeight = 34;
    const size = (moduleCount + quietZone * 2) * scale;
    canvas.width = size;
    canvas.height = size + labelHeight;

    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#05070b';

    modules.forEach((row, y) => {
      row.forEach((dark, x) => {
        if (dark) {
          ctx.fillRect((x + quietZone) * scale, (y + quietZone) * scale, scale, scale);
        }
      });
    });

    ctx.font = 'bold 14px Segoe UI, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(label, canvas.width / 2, canvas.height - 13);
  }

  function navEntries() {
    const role = seed.currentUser?.accountRole || 'staff';
    if (role === 'admin') {
      return [
        { label: 'Dashboard', detail: 'Admin command center', url: route('/admin/dashboard') },
        { label: 'QR Management', detail: 'Generate and revoke global QR codes', url: route('/admin/qr') },
        { label: 'Attendance Logs', detail: 'Search, review, and export records', url: route('/admin/attendance') },
        { label: 'User Management', detail: 'Add, edit, activate, and disable employees', url: route('/admin/users') },
        { label: 'Calendar', detail: 'Monthly workforce markers', url: route('/admin/calendar') },
        { label: 'Settings', detail: 'Company and attendance rules', url: route('/admin/settings') },
      ];
    }

    return [
      { label: 'Dashboard', detail: 'Your attendance snapshot', url: route('/staff/dashboard') },
      { label: 'Scan QR', detail: 'Clock in or clock out', url: route('/staff/scan-qr') },
      { label: 'History', detail: 'Personal attendance records', url: route('/staff/history') },
      { label: 'Calendar', detail: 'Schedule and attendance markers', url: route('/staff/calendar') },
      { label: 'Profile', detail: 'Employee and account settings', url: route('/staff/profile') },
    ];
  }

  function globalSearchResults(query) {
    const normalized = query.trim().toLowerCase();
    const pages = navEntries().map((entry) => ({ ...entry, type: 'Page' }));
    const people = users().map((user) => ({
      type: 'Employee',
      label: user.name,
      detail: `${user.employeeId} / ${user.department} / ${user.status}`,
      url: seed.currentUser?.accountRole === 'admin' ? route('/admin/users') : route('/staff/profile'),
    }));
    const attendance = records().slice(0, 20).map((record) => ({
      type: 'Attendance',
      label: `${record.employeeName} / ${record.type}`,
      detail: `${formatDateTime(record.timestamp)} / ${record.status}`,
      url: seed.currentUser?.accountRole === 'admin' ? route('/admin/attendance') : route('/staff/history'),
    }));

    const all = [...pages, ...people, ...attendance];
    if (!normalized) {
      return pages.slice(0, 6);
    }

    return all
      .filter((item) => `${item.type} ${item.label} ${item.detail}`.toLowerCase().includes(normalized))
      .slice(0, 8);
  }

  function notificationItems() {
    const itemList = records();
    const metrics = dashboardMetrics(itemList, users());
    const pending = itemList.filter((record) => record.status === 'Pending Review' || record.syncStatus === 'Pending');
    const items = [];

    if (metrics.currentlyOnsite > 0) {
      items.push({
        level: 'teal',
        title: `${metrics.currentlyOnsite} currently onsite`,
        detail: 'Live attendance state is active.',
        url: seed.currentUser?.accountRole === 'admin' ? route('/admin/dashboard') : route('/staff/dashboard'),
      });
    }

    pending.slice(0, 3).forEach((record) => {
      items.push({
        level: 'amber',
        title: `${record.employeeName} needs review`,
        detail: `${record.type} / ${record.device}`,
        url: route('/admin/attendance'),
      });
    });

    if (!items.length) {
      items.push({
        level: 'violet',
        title: 'No urgent alerts',
        detail: 'Attendance data is currently in sync.',
        url: route(seed.currentUser?.accountRole === 'admin' ? '/admin/dashboard' : '/staff/dashboard'),
      });
    }

    return items;
  }

  window.ClockIt = {
    route,
    read,
    write,
    users,
    records,
    qrTokens,
    settings,
    saveUsers,
    saveRecords,
    saveQrTokens,
    saveSettings,
    helpers: {
      dateKey,
      formatDate,
      formatTime,
      formatDateTime,
      statusClass,
      currentStatus,
      workSummary,
      dashboardMetrics,
      departmentPresence,
      calendarMarkersFor,
      downloadCsv,
    },
  };

  window.appShell = function () {
    return {
      sidebarCollapsed: localStorage.getItem(prefix + 'sidebarCollapsed') === 'true',
      focusMode: localStorage.getItem(prefix + 'focusMode') === 'true',
      mobileNavOpen: false,
      searchOpen: false,
      notificationOpen: false,
      globalSearch: '',
      init() {
        document.body.classList.toggle('focus-mode', this.focusMode);
        this.$watch('sidebarCollapsed', (value) => {
          localStorage.setItem(prefix + 'sidebarCollapsed', value ? 'true' : 'false');
        });
        this.$watch('focusMode', (value) => {
          localStorage.setItem(prefix + 'focusMode', value ? 'true' : 'false');
          document.body.classList.toggle('focus-mode', value);
        });
        window.addEventListener('keydown', (event) => {
          if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName || '')) {
            event.preventDefault();
            this.openGlobalSearch();
          }
        });
      },
      get globalResults() {
        return globalSearchResults(this.globalSearch);
      },
      get notifications() {
        return notificationItems();
      },
      get unreadNotifications() {
        return this.notifications.filter((item) => item.level !== 'violet').length;
      },
      openGlobalSearch() {
        this.searchOpen = true;
        this.notificationOpen = false;
        this.$nextTick(() => this.$refs.globalSearchInput?.focus());
      },
      closeGlobalSearch() {
        this.searchOpen = false;
        this.globalSearch = '';
      },
      openResult(result) {
        if (result?.url) {
          window.location.href = result.url;
        }
      },
      openFirstResult() {
        if (this.globalResults.length) {
          this.openResult(this.globalResults[0]);
        }
      },
      toggleNotifications() {
        this.notificationOpen = !this.notificationOpen;
        this.searchOpen = false;
      },
      goToProfile() {
        window.location.href = route(seed.currentUser?.accountRole === 'admin' ? '/admin/settings' : '/staff/profile');
      },
      toggleFocusMode() {
        this.focusMode = !this.focusMode;
      },
    };
  };

  window.loginPanel = function () {
    return {
      role: 'staff',
      identifier: 'tentsaolo.khoza@clock-it.local',
      password: 'demo-access',
      error: '',
      setRole(role) {
        this.role = role;
        this.identifier = role === 'admin' ? 'admin@clock-it.local' : 'tentsaolo.khoza@clock-it.local';
      },
      submit(event) {
        this.error = '';
        if (!this.identifier.trim() || !this.password.trim()) {
          event.preventDefault();
          this.error = 'Email and password are required.';
        }
      },
    };
  };

  window.adminDashboard = function () {
    return {
      users: [],
      records: [],
      init() {
        this.refresh();
      },
      refresh() {
        this.users = users();
        this.records = records();
      },
      get metrics() {
        return dashboardMetrics(this.records, this.users);
      },
      get metricCards() {
        return [
          { label: 'Currently onsite', value: this.metrics.currentlyOnsite, helper: 'Active staff on premises', accent: 'teal' },
          { label: 'Clocked in today', value: this.metrics.totalClockedInToday, helper: 'Unique clock-in employees', accent: 'violet' },
          { label: 'Pending sync/review', value: this.metrics.pendingSyncReview, helper: 'Records needing attention', accent: 'amber' },
          { label: 'Events today', value: this.metrics.totalEventsToday, helper: 'Clock in and out scans', accent: 'coral' },
        ];
      },
      get recentActivity() {
        return sortedRecords(todaysRecords(this.records)).slice(0, 7);
      },
      get onsiteStaff() {
        return this.users
          .filter((user) => user.accountRole === 'staff' && user.status === 'Active')
          .filter((user) => currentStatus(user.employeeId, this.records) === 'Clocked In')
          .map((user) => ({ ...user, summary: workSummary(user.employeeId, today(), this.records) }));
      },
      get departments() {
        return departmentPresence(this.records, this.users);
      },
      statusClass,
      formatTime,
      route,
    };
  };

  window.attendanceManager = function () {
    return {
      users: [],
      records: [],
      search: '',
      employeeFilter: '',
      statusFilter: '',
      deviceFilter: '',
      dateFilter: today(),
      selected: null,
      reviewNote: '',
      init() {
        this.users = users();
        this.records = records();
      },
      get staffOptions() {
        return this.users.filter((user) => user.accountRole === 'staff');
      },
      get filteredRecords() {
        const query = this.search.trim().toLowerCase();
        return sortedRecords(this.records).filter((record) => {
          const matchesQuery = !query || [record.employeeName, record.employeeId, record.department, record.type, record.notes]
            .join(' ')
            .toLowerCase()
            .includes(query);
          const matchesEmployee = !this.employeeFilter || record.employeeId === this.employeeFilter;
          const matchesStatus = !this.statusFilter || record.status === this.statusFilter || record.syncStatus === this.statusFilter;
          const matchesDevice = !this.deviceFilter || record.device === this.deviceFilter;
          const matchesDate = !this.dateFilter || dateKey(record.timestamp) === this.dateFilter;
          return matchesQuery && matchesEmployee && matchesStatus && matchesDevice && matchesDate;
        });
      },
      openReview(record) {
        this.selected = clone(record);
        this.reviewNote = '';
        this.$nextTick(() => showModal('reviewModal'));
      },
      saveReview(status) {
        if (!this.selected) {
          return;
        }
        const stamp = new Date().toISOString();
        this.records = this.records.map((record) => {
          if (record.id !== this.selected.id) {
            return record;
          }
          const auditTrail = record.auditTrail || [];
          return {
            ...record,
            status,
            syncStatus: status === 'Verified' ? 'Synced' : record.syncStatus,
            notes: this.reviewNote ? `${record.notes || ''} ${this.reviewNote}`.trim() : record.notes,
            updatedAt: stamp,
            auditTrail: [
              ...auditTrail,
              {
                at: stamp,
                actor: seed.currentUser?.name || 'Admin',
                event: `${status} review applied${this.reviewNote ? `: ${this.reviewNote}` : '.'}`,
              },
            ],
          };
        });
        saveRecords(this.records);
        this.records = records();
        hideModal('reviewModal');
      },
      exportCsv() {
        const header = ['id', 'employeeId', 'employeeName', 'department', 'role', 'type', 'timestamp', 'device', 'qrUsed', 'status', 'syncStatus', 'createdAt', 'updatedAt', 'notes'];
        const rows = this.filteredRecords.map((record) => header.map((key) => record[key]));
        downloadCsv(`attendance-${this.dateFilter || 'all'}.csv`, [header, ...rows]);
      },
      statusClass,
      formatDateTime,
    };
  };

  window.userManager = function () {
    const blankForm = () => ({
      id: '',
      employeeId: '',
      name: '',
      email: '',
      department: '',
      jobTitle: '',
      role: 'Staff',
      accountRole: 'staff',
      status: 'Active',
      phone: '',
      location: '',
      startDate: today(),
      manager: '',
      avatar: '',
    });

    return {
      users: [],
      records: [],
      search: '',
      roleFilter: '',
      statusFilter: '',
      mode: 'add',
      form: blankForm(),
      selected: null,
      error: '',
      init() {
        this.users = users();
        this.records = records();
      },
      get filteredUsers() {
        const query = this.search.trim().toLowerCase();
        return this.users.filter((user) => {
          const matchesQuery = !query || [user.name, user.employeeId, user.email, user.department, user.jobTitle]
            .join(' ')
            .toLowerCase()
            .includes(query);
          const matchesRole = !this.roleFilter || user.accountRole === this.roleFilter;
          const matchesStatus = !this.statusFilter || user.status === this.statusFilter;
          return matchesQuery && matchesRole && matchesStatus;
        });
      },
      openAdd() {
        this.mode = 'add';
        this.form = blankForm();
        this.error = '';
        this.$nextTick(() => showModal('userModal'));
      },
      openEdit(user) {
        this.mode = 'edit';
        this.form = clone(user);
        this.error = '';
        this.$nextTick(() => showModal('userModal'));
      },
      openDetails(user) {
        this.selected = clone(user);
        this.$nextTick(() => showModal('userDetailsModal'));
      },
      saveUser() {
        this.error = '';
        if (!this.form.name.trim() || !this.form.email.trim() || !this.form.department.trim()) {
          this.error = 'Name, email, and department are required.';
          return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
          this.error = 'Enter a valid email address.';
          return;
        }
        const payload = {
          ...this.form,
          avatar: this.form.avatar || initials(this.form.name),
          role: this.form.accountRole === 'admin' ? 'Admin' : 'Staff',
        };
        if (this.mode === 'add') {
          payload.id = `usr-${Date.now()}`;
          payload.employeeId = payload.employeeId || `EMP-${Math.floor(Math.random() * 700 + 200)}`;
          this.users = [payload, ...this.users];
        } else {
          this.users = this.users.map((user) => (user.id === payload.id ? payload : user));
        }
        saveUsers(this.users);
        hideModal('userModal');
      },
      toggleStatus(user) {
        this.users = this.users.map((item) => (
          item.id === user.id ? { ...item, status: item.status === 'Active' ? 'Disabled' : 'Active' } : item
        ));
        saveUsers(this.users);
      },
      employeeStatus(employeeId) {
        return currentStatus(employeeId, this.records);
      },
      statusClass,
      formatDate,
    };
  };

  window.qrManager = function () {
    return {
      tokens: {},
      feedback: '',
      init() {
        this.tokens = qrTokens();
        this.$nextTick(() => this.drawAll());
      },
      drawAll() {
        drawQr(document.getElementById('qr-clock-in'), this.tokens.clockIn?.token || 'REVOKED', 'GLOBAL CLOCK IN');
        drawQr(document.getElementById('qr-clock-out'), this.tokens.clockOut?.token || 'REVOKED', 'GLOBAL CLOCK OUT');
      },
      tokenFor(type) {
        return this.tokens[type] || {};
      },
      generate(type) {
        const isIn = type === 'clockIn';
        const stamp = new Date();
        const code = `${isIn ? 'GCI' : 'GCO'}-${stamp.getFullYear()}${pad(stamp.getMonth() + 1)}${pad(stamp.getDate())}-${Math.random().toString(36).slice(2, 6).toUpperCase()}`;
        this.tokens[type] = {
          type: isIn ? 'Global Clock In' : 'Global Clock Out',
          token: code,
          status: 'Active',
          generatedAt: stamp.toISOString(),
          expiresAt: new Date(stamp.getTime() + 14 * 60 * 60 * 1000).toISOString(),
          createdBy: seed.currentUser?.name || 'Admin',
          usageCount: 0,
        };
        saveQrTokens(this.tokens);
        this.$nextTick(() => this.drawAll());
      },
      regenerate(type) {
        this.generate(type);
      },
      revoke(type) {
        this.tokens[type] = {
          ...this.tokens[type],
          status: 'Revoked',
          revokedAt: new Date().toISOString(),
        };
        saveQrTokens(this.tokens);
        this.$nextTick(() => this.drawAll());
      },
      download(type) {
        const canvas = document.getElementById(type === 'clockIn' ? 'qr-clock-in' : 'qr-clock-out');
        if (!canvas) {
          return;
        }
        const link = document.createElement('a');
        link.download = `${type === 'clockIn' ? 'global-clock-in' : 'global-clock-out'}-qr.png`;
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        link.remove();
      },
      copyToken(type) {
        const token = this.tokens[type]?.token || '';
        if (!token) {
          this.feedback = 'No token is available to copy.';
          return;
        }

        const done = () => {
          this.feedback = `${this.tokens[type].type} token copied: ${token}`;
          setTimeout(() => {
            this.feedback = '';
          }, 3000);
        };

        if (navigator.clipboard?.writeText) {
          navigator.clipboard.writeText(token).then(done).catch(() => {
            this.feedback = token;
          });
          return;
        }

        this.feedback = token;
      },
      statusClass,
      formatDateTime,
    };
  };

  window.calendarView = function (scope) {
    const base = new Date(`${today()}T00:00:00`);
    return {
      scope,
      records: [],
      monthIndex: base.getMonth(),
      year: base.getFullYear(),
      selectedDate: today(),
      weekdays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      init() {
        this.records = records();
      },
      get months() {
        return [
          'January', 'February', 'March', 'April', 'May', 'June',
          'July', 'August', 'September', 'October', 'November', 'December',
        ];
      },
      get years() {
        const current = new Date().getFullYear();
        return [current - 1, current, current + 1, current + 2];
      },
      get monthLabel() {
        return `${this.months[this.monthIndex]} ${this.year}`;
      },
      get days() {
        const first = new Date(this.year, Number(this.monthIndex), 1);
        const daysInMonth = new Date(this.year, Number(this.monthIndex) + 1, 0).getDate();
        const startOffset = (first.getDay() + 6) % 7;
        const output = [];

        for (let i = 0; i < startOffset; i += 1) {
          output.push({ empty: true, key: `empty-${i}` });
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
          const key = `${this.year}-${pad(Number(this.monthIndex) + 1)}-${pad(day)}`;
          output.push({
            key,
            day,
            empty: false,
            today: key === today(),
            selected: key === this.selectedDate,
            markers: calendarMarkersFor(key, this.scope, this.records),
          });
        }

        return output;
      },
      get selectedMarkers() {
        return calendarMarkersFor(this.selectedDate, this.scope, this.records);
      },
      get selectedRecords() {
        const currentUser = seed.currentUser || {};
        return sortedRecords(this.records).filter((record) => {
          if (dateKey(record.timestamp) !== this.selectedDate) {
            return false;
          }
          return this.scope === 'staff' ? record.employeeId === currentUser.employeeId : true;
        });
      },
      selectDay(day) {
        if (!day.empty) {
          this.selectedDate = day.key;
        }
      },
      markerClass(type) {
        return `marker marker-${type}`;
      },
      formatDate,
      formatTime,
      statusClass,
    };
  };

  window.settingsManager = function () {
    return {
      settings: {},
      saved: false,
      error: '',
      init() {
        this.settings = settings();
      },
      save() {
        this.error = '';
        if (!this.settings.company?.name || !this.settings.company?.timezone) {
          this.error = 'Company name and timezone are required.';
          return;
        }
        saveSettings(this.settings);
        this.saved = true;
        setTimeout(() => {
          this.saved = false;
        }, 2500);
      },
    };
  };

  window.staffDashboard = function () {
    return {
      currentUser: seed.currentUser || {},
      records: [],
      init() {
        this.records = records();
      },
      get summary() {
        return workSummary(this.currentUser.employeeId, today(), this.records);
      },
      get recentHistory() {
        return sortedRecords(this.records.filter((record) => record.employeeId === this.currentUser.employeeId)).slice(0, 6);
      },
      get upcomingSchedule() {
        return (calendarSeed().schedule || [])
          .filter((item) => item.employeeId === this.currentUser.employeeId && item.date >= today())
          .slice(0, 4);
      },
      get todayEvents() {
        return this.summary.records;
      },
      get totalHoursLabel() {
        return `${this.summary.totalHours.toFixed(1)}h`;
      },
      route,
      formatTime,
      formatDate,
      formatDateTime,
      statusClass,
    };
  };

  window.scanQr = function () {
    return {
      currentUser: seed.currentUser || {},
      records: [],
      tokens: {},
      manualCode: '',
      feedback: null,
      init() {
        this.records = records();
        this.tokens = qrTokens();
      },
      get status() {
        return currentStatus(this.currentUser.employeeId, this.records);
      },
      get summary() {
        return workSummary(this.currentUser.employeeId, today(), this.records);
      },
      scan(type) {
        const token = this.tokens[type];
        if (!token || token.status !== 'Active') {
          this.feedback = { level: 'danger', message: 'The selected global QR is revoked or unavailable.' };
          return;
        }

        const wantsIn = type === 'clockIn';
        if (wantsIn && this.status === 'Clocked In') {
          this.feedback = { level: 'warning', message: 'You are already clocked in. Clock out before starting a new session.' };
          return;
        }
        if (!wantsIn && this.status === 'Clocked Out') {
          this.feedback = { level: 'warning', message: 'You are already clocked out. Clock in before submitting a clock out.' };
          return;
        }

        const newRecord = createAttendanceRecord(type, this.currentUser, token.token);
        const nextRecords = [...this.records, newRecord];
        this.records = enforceConsistency(nextRecords);
        saveRecords(this.records);

        this.tokens[type] = { ...token, usageCount: Number(token.usageCount || 0) + 1 };
        saveQrTokens(this.tokens);

        this.feedback = {
          level: 'success',
          message: wantsIn ? 'Clock in recorded. Your clock out is now pending.' : 'Clock out recorded. Your shift is closed.',
        };
      },
      scanManual() {
        const code = this.manualCode.trim().toUpperCase();
        if (!code) {
          this.feedback = { level: 'danger', message: 'Enter a QR token or scan label.' };
          return;
        }
        if (code.includes('CLOCK_IN') || code.includes('GCI') || code === String(this.tokens.clockIn?.token || '').toUpperCase()) {
          this.scan('clockIn');
          return;
        }
        if (code.includes('CLOCK_OUT') || code.includes('GCO') || code === String(this.tokens.clockOut?.token || '').toUpperCase()) {
          this.scan('clockOut');
          return;
        }
        this.feedback = { level: 'danger', message: 'QR token does not match an active global clock flow.' };
      },
      formatTime,
      statusClass,
    };
  };

  window.historyView = function () {
    return {
      currentUser: seed.currentUser || {},
      records: [],
      view: 'list',
      search: '',
      typeFilter: '',
      dateFilter: '',
      selected: null,
      init() {
        this.records = records();
      },
      get ownRecords() {
        return sortedRecords(this.records.filter((record) => record.employeeId === this.currentUser.employeeId));
      },
      get filteredRecords() {
        const query = this.search.trim().toLowerCase();
        return this.ownRecords.filter((record) => {
          const matchesQuery = !query || [record.type, record.device, record.status, record.notes].join(' ').toLowerCase().includes(query);
          const matchesType = !this.typeFilter || record.type === this.typeFilter;
          const matchesDate = !this.dateFilter || dateKey(record.timestamp) === this.dateFilter;
          return matchesQuery && matchesType && matchesDate;
        });
      },
      openDetails(record) {
        this.selected = clone(record);
        this.$nextTick(() => showModal('historyDetailsModal'));
      },
      exportCsv() {
        const header = ['id', 'employeeId', 'employeeName', 'department', 'role', 'type', 'timestamp', 'device', 'qrUsed', 'status', 'syncStatus', 'createdAt', 'updatedAt', 'notes'];
        const rows = this.filteredRecords.map((record) => header.map((key) => record[key]));
        downloadCsv(`my-attendance-${this.dateFilter || 'all'}.csv`, [header, ...rows]);
      },
      dateKey,
      formatDateTime,
      statusClass,
    };
  };

  window.profileView = function () {
    return {
      users: [],
      form: {},
      saved: false,
      error: '',
      init() {
        this.users = users();
        const current = employeeById(seed.currentUser?.employeeId, this.users) || seed.currentUser || {};
        this.form = clone(current);
      },
      save() {
        this.error = '';
        if (!this.form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
          this.error = 'Enter a valid email address.';
          return;
        }
        if (!this.form.phone || this.form.phone.length < 8) {
          this.error = 'Enter a valid contact number.';
          return;
        }
        this.form.avatar = this.form.avatar || initials(this.form.name);
        this.users = this.users.map((user) => (user.employeeId === this.form.employeeId ? { ...user, ...this.form } : user));
        saveUsers(this.users);
        this.saved = true;
        setTimeout(() => {
          this.saved = false;
        }, 2500);
      },
      formatDate,
    };
  };

  document.addEventListener('click', (event) => {
    const dismiss = event.target.closest('[data-bs-dismiss="modal"]');
    if (dismiss) {
      const modal = dismiss.closest('.modal');
      if (modal) {
        event.preventDefault();
        hideModal(modal.id);
      }
      return;
    }

    const modal = event.target.classList?.contains('modal') ? event.target : null;
    if (modal?.classList.contains('show')) {
      hideModal(modal.id);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
      return;
    }
    const openModal = document.querySelector('.modal.show');
    if (openModal) {
      hideModal(openModal.id);
    }
  });
})();
