(function () {
    function normalizeLogData(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        if (!payload || typeof payload !== 'object') {
            return [];
        }

        var knownKeys = ['data', 'logs', 'attendance', 'attendanceLogs', 'clockEvents', 'rows', 'results'];

        for (var index = 0; index < knownKeys.length; index += 1) {
            var value = payload[knownKeys[index]];

            if (Array.isArray(value)) {
                return value;
            }
        }

        return [payload];
    }

    function flattenValue(value) {
        if (value === null || value === undefined) {
            return '';
        }

        if (typeof value === 'object') {
            return JSON.stringify(value);
        }

        return String(value);
    }

    function escapeCsvCell(value) {
        var cell = flattenValue(value);
        var mustQuote = /[",\r\n]/.test(cell);
        var escaped = cell.replace(/"/g, '""');

        return mustQuote ? '"' + escaped + '"' : escaped;
    }

    function buildHeaders(rows) {
        var seen = {};
        var headers = [];

        rows.forEach(function (row) {
            if (!row || typeof row !== 'object' || Array.isArray(row)) {
                if (!seen.value) {
                    seen.value = true;
                    headers.push('value');
                }

                return;
            }

            Object.keys(row).forEach(function (key) {
                if (!seen[key]) {
                    seen[key] = true;
                    headers.push(key);
                }
            });
        });

        return headers;
    }

    function logsToCsv(rows) {
        var safeRows = Array.isArray(rows) ? rows : [];
        var headers = buildHeaders(safeRows);

        if (headers.length === 0) {
            headers = ['message'];
        }

        var lines = [
            headers.map(escapeCsvCell).join(',')
        ];

        safeRows.forEach(function (row) {
            var source = row && typeof row === 'object' && !Array.isArray(row) ? row : { value: row };

            lines.push(headers.map(function (header) {
                return escapeCsvCell(source[header]);
            }).join(','));
        });

        return lines.join('\r\n');
    }

    function todayForFilename() {
        var date = new Date();
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');

        return year + '-' + month + '-' + day;
    }

    function downloadCsv(csvText) {
        var blob = new Blob(['\uFEFF' + csvText], { type: 'text/csv;charset=utf-8' });
        var url = window.URL.createObjectURL(blob);
        var link = document.createElement('a');

        link.href = url;
        link.download = 'attendance_' + todayForFilename() + '.csv';
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }

    window.adminAttendanceExport = function (endpoint) {
        return {
            loading: false,
            error: '',

            exportLogs: async function () {
                var controller = new AbortController();
                var timeoutId = window.setTimeout(function () {
                    controller.abort();
                }, 15000);

                this.loading = true;
                this.error = '';

                try {
                    var response = await fetch(endpoint, {
                        headers: {
                            Accept: 'application/json'
                        },
                        signal: controller.signal
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    var payload = await response.json();
                    var rows = normalizeLogData(payload);
                    var csvText = logsToCsv(rows);

                    downloadCsv(csvText);
                } catch (error) {
                    this.error = error.name === 'AbortError'
                        ? 'Export timed out. Please try again.'
                        : 'Attendance logs could not be exported. Please try again.';
                } finally {
                    window.clearTimeout(timeoutId);
                    this.loading = false;
                }
            }
        };
    };

    window.clockItCsv = {
        normalizeLogData: normalizeLogData,
        logsToCsv: logsToCsv,
        todayForFilename: todayForFilename
    };
}());
