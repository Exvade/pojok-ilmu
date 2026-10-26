export function createQrCamera({ video, onDetected, onError, secure = globalThis.isSecureContext, mediaDevices = globalThis.navigator?.mediaDevices, makeCanvas = () => document.createElement('canvas'), schedule = setTimeout, cancel = clearTimeout, decode = null }) {
    let stream = null;
    let timer = null;
    let generation = 0;

    const stop = () => {
        generation++;
        if (timer !== null) cancel(timer);
        timer = null;
        stream?.getTracks().forEach(track => track.stop());
        stream = null;
        video.srcObject = null;
    };

    const start = async () => {
        stop();
        const current = generation;
        if (!secure) {
            onError('Kamera memerlukan koneksi HTTPS atau localhost. Gunakan alat pemindai atau masukkan NIK sementara ini.');
            return false;
        }
        if (!mediaDevices?.getUserMedia) {
            onError('Browser ini tidak mendukung akses kamera. Gunakan browser terbaru, alat pemindai, atau masukkan NIK.');
            return false;
        }

        try {
            const captured = await mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            if (current !== generation) {
                captured.getTracks().forEach(track => track.stop());
                return false;
            }
            stream = captured;
            video.srcObject = captured;
            await video.play();
            if (current !== generation) return false;
            const decodeFrame = decode || (await import('jsqr')).default;
            if (current !== generation) return false;
            const canvas = makeCanvas();
            const context = canvas.getContext('2d', { willReadFrequently: true });
            if (!context) throw new Error('camera_canvas_unavailable');

            const scan = () => {
                if (current !== generation) return;
                try {
                    if (video.readyState >= 2 && video.videoWidth && video.videoHeight) {
                        const scale = Math.min(1, 640 / video.videoWidth);
                        canvas.width = Math.round(video.videoWidth * scale);
                        canvas.height = Math.round(video.videoHeight * scale);
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const frame = context.getImageData(0, 0, canvas.width, canvas.height);
                        const code = decodeFrame(frame.data, frame.width, frame.height, { inversionAttempts: 'attemptBoth' });
                        if (code?.data?.trim()) {
                            stop();
                            onDetected(code.data.trim());
                            return;
                        }
                    }
                    timer = schedule(scan, 200);
                } catch {
                    stop();
                    onError('Kamera aktif, tetapi gambar tidak dapat dipindai. Coba ulangi atau gunakan NIK.');
                }
            };
            timer = schedule(scan, 200);
            return true;
        } catch (error) {
            if (current !== generation) return false;
            stop();
            if (error.name === 'NotAllowedError' || error.name === 'SecurityError') {
                onError('Izin kamera ditolak. Izinkan kamera pada pengaturan browser, lalu coba lagi.');
            } else if (error.name === 'NotFoundError' || error.name === 'OverconstrainedError') {
                onError('Kamera tidak ditemukan. Periksa kamera perangkat atau gunakan NIK.');
            } else if (error.name === 'NotReadableError') {
                onError('Kamera sedang digunakan atau tidak dapat dibuka. Tutup aplikasi lain lalu coba lagi.');
            } else {
                onError('Kamera belum dapat digunakan. Periksa perangkat dan izin browser, lalu coba lagi.');
            }
            return false;
        }
    };

    return { start, stop };
}
