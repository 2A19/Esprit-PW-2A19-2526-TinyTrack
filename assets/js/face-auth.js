/**
 * TinyTrack — Face authentication client module
 *
 * Uses face-api.js (vladmandic fork) to:
 *   - Load detection + recognition models once
 *   - Open the webcam in a modal
 *   - Capture a face descriptor (128-D vector) from the current frame
 *   - POST the descriptor to /View/auth/face_login.php or face_enroll.php
 *
 * The descriptor is IRREVERSIBLE : impossible to reconstruct the face from it.
 */

const FaceAuth = (() => {
  const MODEL_BASE = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
  let modelsLoaded = false;
  let loadingPromise = null;

  /** Lazy-loads the face-api models (once per page). */
  async function loadModels() {
    if (modelsLoaded) return;
    if (loadingPromise) return loadingPromise;
    loadingPromise = (async () => {
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_BASE),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_BASE),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_BASE),
      ]);
      modelsLoaded = true;
    })();
    return loadingPromise;
  }

  /** Start the webcam on the given <video> element. Returns the stream. */
  async function startCamera(video) {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { width: 480, height: 360, facingMode: 'user' },
      audio: false,
    });
    video.srcObject = stream;
    await new Promise((resolve) => {
      video.onloadedmetadata = () => { video.play(); resolve(); };
    });
    return stream;
  }

  function stopCamera(stream) {
    if (stream) stream.getTracks().forEach((t) => t.stop());
  }

  /**
   * Detect a single face on the video element and return its 128-D descriptor.
   * Returns null if no face (or multiple faces) were detected.
   */
  async function captureDescriptor(video) {
    const detection = await faceapi
      .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
      .withFaceLandmarks()
      .withFaceDescriptor();
    if (!detection) return null;
    return Array.from(detection.descriptor);
  }

  /** Average N descriptors element-wise (used during enrollment). */
  function averageDescriptors(list) {
    const out = new Array(128).fill(0);
    for (const d of list) {
      for (let i = 0; i < 128; i++) out[i] += d[i];
    }
    for (let i = 0; i < 128; i++) out[i] /= list.length;
    return out;
  }

  async function postJSON(url, body) {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    return r.json();
  }

  return {
    loadModels,
    startCamera,
    stopCamera,
    captureDescriptor,
    averageDescriptors,
    postJSON,
  };
})();
