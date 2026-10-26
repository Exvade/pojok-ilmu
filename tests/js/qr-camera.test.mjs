import test from 'node:test';
import assert from 'node:assert/strict';
import { createQrCamera } from '../../resources/js/qr-camera.js';

function setup(overrides = {}) {
    let stopped = 0;
    let scheduled = null;
    const results = [];
    const errors = [];
    const stream = { getTracks: () => [{ stop: () => stopped++ }] };
    const video = { srcObject: null, readyState: 2, videoWidth: 640, videoHeight: 480, play: async () => {} };
    const camera = createQrCamera({
        video, secure: true, mediaDevices: { getUserMedia: async () => stream },
        makeCanvas: () => ({ getContext: () => ({ drawImage() {}, getImageData: () => ({ data: new Uint8ClampedArray(4), width: 1, height: 1 }) }) }),
        schedule: callback => { scheduled = callback; return 1; }, cancel: () => { scheduled = null; },
        decode: () => ({ data: ' NIK-123 ' }), onDetected: code => results.push(code), onError: error => errors.push(error),
        ...overrides,
    });
    return { camera, stream, video, results, errors, stopped: () => stopped, scan: () => scheduled?.() };
}

test('memindai QR satu kali dan menutup kamera sebelum mengirim hasil', async () => {
    const fixture = setup();
    assert.equal(await fixture.camera.start(), true);
    assert.equal(fixture.video.srcObject, fixture.stream);
    fixture.scan();
    assert.deepEqual(fixture.results, ['NIK-123']);
    assert.equal(fixture.stopped(), 1);
    assert.equal(fixture.video.srcObject, null);
    fixture.scan();
    assert.deepEqual(fixture.results, ['NIK-123']);
});

test('menjelaskan ketika HTTP tidak mendukung akses kamera', async () => {
    const fixture = setup({ secure: false });
    assert.equal(await fixture.camera.start(), false);
    assert.match(fixture.errors[0], /HTTPS atau localhost/);
    assert.equal(fixture.stopped(), 0);
});

test('menjelaskan ketika izin kamera ditolak', async () => {
    const fixture = setup({ mediaDevices: { getUserMedia: async () => { throw Object.assign(new Error(), { name: 'NotAllowedError' }); } } });
    assert.equal(await fixture.camera.start(), false);
    assert.match(fixture.errors[0], /Izin kamera ditolak/);
});

test('membatalkan permintaan yang masih menunggu dan menutup kamera yang terlambat diberikan', async () => {
    let resolvePermission;
    const fixture = setup({ mediaDevices: { getUserMedia: () => new Promise(resolve => { resolvePermission = resolve; }) } });
    const starting = fixture.camera.start();
    fixture.camera.stop();
    resolvePermission(fixture.stream);
    assert.equal(await starting, false);
    assert.equal(fixture.stopped(), 1);
    assert.equal(fixture.video.srcObject, null);
});
