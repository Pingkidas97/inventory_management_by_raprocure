class Exporter {
    constructor(options) {
        this.chunkSize = options.chunkSize || 1000;
        this.rowLimitPerSheet = options.rowLimitPerSheet || 200000;
        this.headers = options.headers || [];
        this.totalUrl = options.totalUrl;
        this.batchUrl = options.batchUrl;
        this.token = options.token || null;
        this.getParams = options.getParams || (() => ({}));
        this.exportName = options.exportName || 'Export';
        this.expButton = options.expButton || null;
        this.exportProgress='#export-progress';
        this.progressText='#progress-text';
        this.progress='#progress';
        this.fillterReadOnly='.fillter-form-control';

        this.sheetCount = 1;
        this.currentSheetRows = 0;
        this.currentProgress = 0;
        this.currentChunk = 0;
        this.totalChunks = 0;
        this.currentSheet = XLSX.utils.book_new();
    }

    start() {
        if (!this.expButton || $(this.expButton).is(':disabled')) return;

        $(this.expButton).attr('disabled', true);
        $(this.exportProgress).show();
        $(this.progressText).text('Progress: 0%');
        $(this.fillterReadOnly).attr('readonly', true);

        $.ajax({
            url: this.totalUrl,
            type: 'GET',
            data: {
                ...this.getParams(),
                _token: this.token
            },
            dataType: 'json',
            success: (response) => {
                this.totalChunks = Math.ceil(response.total / this.chunkSize);
                this.fetchChunk(this.currentChunk);
            },
            error: () => {
                this.showError('Error fetching total count');
            }
        });
    }

    fetchChunk(chunkIndex) {
        $.ajax({
            url: this.batchUrl,
            type: 'GET',
            data: {
                ...this.getParams(),
                start: chunkIndex * this.chunkSize,
                limit: this.chunkSize,
                _token: this.token
            },
            dataType: 'json',
            success: (response) => {
                const data = response.data;
                if (data.length > 0) {
                    if (this.currentSheetRows === 0) {
                        this.currentSheet.Sheets[`Sheet${this.sheetCount}`] = XLSX.utils.aoa_to_sheet([this.headers]);
                        this.currentSheet.SheetNames = [`Sheet${this.sheetCount}`];
                    }

                    data.forEach(row => {
                        if (this.currentSheetRows >= this.rowLimitPerSheet) {
                            this.sheetCount++;
                            this.currentSheet.Sheets[`Sheet${this.sheetCount}`] = XLSX.utils.aoa_to_sheet([this.headers]);
                            this.currentSheet.SheetNames.push(`Sheet${this.sheetCount}`);
                            this.currentSheetRows = 0;
                        }
                        XLSX.utils.sheet_add_aoa(this.currentSheet.Sheets[`Sheet${this.sheetCount}`], [row], { origin: -1 });
                        this.currentSheetRows++;
                    });
                }

                this.currentProgress = Math.round(((this.currentChunk + 1) / this.totalChunks) * 100);
                $(this.progress).css('width', this.currentProgress + '%');
                $(this.progressText).text('Progress: ' + this.currentProgress + '%');
                this.currentChunk++;

                if (this.currentChunk < this.totalChunks) {
                    this.fetchChunk(this.currentChunk);
                } else {
                    this.finish();
                }
            },
            error: () => {
                this.showError('Error fetching data chunk');
            }
        });
    }

    finish() {
        if (Object.keys(this.currentSheet.Sheets).length === 0) {
            this.showError('No data to export!');
            return;
        }

        const now = new Date();
        const date = now.toLocaleDateString('en-GB').replace(/\//g, '-');
        const time = now.toLocaleTimeString('en-GB', { hour12: false }).replace(/:/g, '-');
        const fileName = `${this.exportName}-${date}-${time}.xlsx`;

        const wbout = XLSX.write(this.currentSheet, { bookType: 'xlsx', type: 'binary' });
        const blob = new Blob([this.s2ab(wbout)], { type: 'application/octet-stream' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = fileName;
        link.click();

        $(this.exportProgress).hide();
        $(this.progress).css('width', '0%');
        $(this.fillterReadOnly).attr('readonly', false);
        $(this.expButton).attr('disabled', false);
    }

    showError(message) {
        alert(message);
        $(this.exportProgress).hide();
        $(this.progressText).text('Progress: 0%');
        $(this.progress).css('width', '0%');
        $(this.fillterReadOnly).attr('readonly', false);
        $(this.expButton).attr('disabled', false);
    }

    s2ab(s) {
        const buf = new ArrayBuffer(s.length);
        const view = new Uint8Array(buf);
        for (let i = 0; i < s.length; i++) {
            view[i] = s.charCodeAt(i) & 0xff;
        }
        return buf;
    }
}
