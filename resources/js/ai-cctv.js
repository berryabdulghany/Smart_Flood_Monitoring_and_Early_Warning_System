const AI_ENDPOINT = 'http://127.0.0.1:5000/detect';
const DETECTION_INTERVAL_MS = 3000;
const AI_REQUEST_TIMEOUT_MS = 7000;
const HISTORY_LIMIT = 6;

let aiDetectionTimer = null;
let isDetecting = false;
let isProcessing = false;
let aiElements = {};

const queryAiElements = () => {
    aiElements = {
        video: document.getElementById('ai-cctv-video'),
        source: document.getElementById('ai-video-source'),
        canvas: document.getElementById('ai-frame-canvas'),
        startButton: document.getElementById('ai-start-detection'),
        stopButton: document.getElementById('ai-stop-detection'),
        processingOverlay: document.getElementById('ai-processing-overlay'),
        videoBadge: document.getElementById('ai-video-badge'),
        engineStatusPill: document.getElementById('ai-engine-status-pill'),
        engineStatusIndicator: document.getElementById('ai-engine-status-indicator'),
        engineStatus: document.getElementById('ai-engine-status'),
        detectionStatus: document.getElementById('ai-detection-status'),
        confidenceValue: document.getElementById('ai-confidence-value'),
        confidenceBar: document.getElementById('ai-confidence-bar'),
        detectionTimestamp: document.getElementById('ai-detection-timestamp'),
        alertCard: document.getElementById('ai-alert-card'),
        alertIcon: document.getElementById('ai-alert-icon'),
        alertTitle: document.getElementById('ai-alert-title'),
        alertMessage: document.getElementById('ai-alert-message'),
        history: document.getElementById('ai-detection-history'),
        error: document.getElementById('ai-error'),
    };
};

const hasAiDashboard = () => Boolean(
    aiElements.video
    && aiElements.canvas
    && aiElements.startButton
    && aiElements.stopButton
);

const endpoint = () => window.SFMEWS?.aiEndpoint || AI_ENDPOINT;

const formatAiTimestamp = (date = new Date()) => new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    timeZone: 'Asia/Jakarta',
}).format(date) + ' WIB';

const normalizeStatus = (payload) => {
    const detection = Array.isArray(payload?.detections) ? payload.detections[0] : null;
    const rawStatus = String(payload?.status || detection?.status || 'TIDAK BANJIR').toUpperCase();
    const status = rawStatus.includes('BANJIR') && !rawStatus.includes('TIDAK') ? 'BANJIR' : 'TIDAK BANJIR';
    const rawConfidence = Number(detection?.confidence ?? payload?.confidence ?? 0);
    const confidence = rawConfidence <= 1 ? rawConfidence * 100 : rawConfidence;

    return {
        status,
        confidence: Number.isFinite(confidence) ? Math.round(confidence) : 0,
    };
};

const setEngineConnection = (isOnline, label = null) => {
    aiElements.engineStatusPill?.classList.toggle('bg-slate-100', false);
    aiElements.engineStatusPill?.classList.toggle('text-slate-600', false);
    aiElements.engineStatusPill?.classList.toggle('ring-slate-200', false);
    aiElements.engineStatusPill?.classList.toggle('bg-emerald-50', isOnline);
    aiElements.engineStatusPill?.classList.toggle('text-emerald-700', isOnline);
    aiElements.engineStatusPill?.classList.toggle('ring-emerald-100', isOnline);
    aiElements.engineStatusPill?.classList.toggle('bg-red-50', !isOnline);
    aiElements.engineStatusPill?.classList.toggle('text-red-700', !isOnline);
    aiElements.engineStatusPill?.classList.toggle('ring-red-100', !isOnline);

    aiElements.engineStatusIndicator?.classList.toggle('bg-slate-400', false);
    aiElements.engineStatusIndicator?.classList.toggle('bg-emerald-500', isOnline);
    aiElements.engineStatusIndicator?.classList.toggle('bg-red-500', !isOnline);
    aiElements.engineStatusIndicator?.classList.toggle('animate-pulse', isOnline);

    if (aiElements.engineStatus) {
        aiElements.engineStatus.textContent = label || (isOnline ? 'AI Online' : 'AI Offline');
    }

    aiElements.error?.classList.toggle('hidden', isOnline);
};

const setProcessingState = (isActive) => {
    aiElements.processingOverlay?.classList.toggle('hidden', !isActive);
    aiElements.processingOverlay?.classList.toggle('grid', isActive);

    if (aiElements.videoBadge) {
        aiElements.videoBadge.textContent = isActive ? 'Analyzing frame with YOLOv8...' : (isDetecting ? 'Realtime AI detection active' : 'Ready for AI detection');
    }
};

const setButtons = () => {
    if (aiElements.startButton) {
        aiElements.startButton.disabled = isDetecting;
        aiElements.startButton.classList.toggle('opacity-60', isDetecting);
        aiElements.startButton.classList.toggle('cursor-not-allowed', isDetecting);
    }

    if (aiElements.stopButton) {
        aiElements.stopButton.disabled = !isDetecting;
        aiElements.stopButton.classList.toggle('opacity-60', !isDetecting);
        aiElements.stopButton.classList.toggle('cursor-not-allowed', !isDetecting);
    }
};

const updateAlert = (status, confidence) => {
    const isFlood = status === 'BANJIR';

    aiElements.alertCard?.classList.toggle('ai-alert-flood', isFlood);
    aiElements.alertCard?.classList.toggle('ai-alert-safe', !isFlood);

    if (aiElements.alertIcon) {
        aiElements.alertIcon.className = `grid h-11 w-11 place-items-center rounded-xl ${isFlood ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'}`;
        aiElements.alertIcon.innerHTML = `<i data-lucide="${isFlood ? 'siren' : 'shield-check'}" class="h-5 w-5"></i>`;
        window.lucide?.createIcons();
    }

    if (aiElements.alertTitle) {
        aiElements.alertTitle.textContent = isFlood ? 'Flood Detected' : 'Safe Condition';
    }

    if (aiElements.alertMessage) {
        aiElements.alertMessage.textContent = isFlood
            ? `AI detected flood indicators with ${confidence}% confidence.`
            : `No flood pattern detected. Confidence ${confidence}%.`;
    }
};

const addHistoryItem = (status, confidence, timestamp) => {
    if (!aiElements.history) {
        return;
    }

    const isFlood = status === 'BANJIR';

    if (aiElements.history.querySelector('.text-slate-500')) {
        aiElements.history.innerHTML = '';
    }

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2';
    item.innerHTML = `
        <div>
            <p class="text-sm font-bold ${isFlood ? 'text-red-600' : 'text-emerald-600'}">${status}</p>
            <p class="text-xs font-medium text-slate-500">${timestamp}</p>
        </div>
        <span class="text-sm font-extrabold text-slate-900">${confidence}%</span>
    `;

    aiElements.history.prepend(item);

    Array.from(aiElements.history.children)
        .slice(HISTORY_LIMIT)
        .forEach((child) => child.remove());
};

const updateDetectionResult = ({ status, confidence }) => {
    const isFlood = status === 'BANJIR';
    const timestamp = formatAiTimestamp();

    if (aiElements.detectionStatus) {
        aiElements.detectionStatus.textContent = status;
        aiElements.detectionStatus.classList.toggle('ai-status-flood', isFlood);
        aiElements.detectionStatus.classList.toggle('ai-status-safe', !isFlood);
    }

    if (aiElements.confidenceValue) {
        aiElements.confidenceValue.textContent = confidence;
        aiElements.confidenceValue.classList.remove('sensor-value-updated');
        window.requestAnimationFrame(() => aiElements.confidenceValue.classList.add('sensor-value-updated'));
    }

    if (aiElements.confidenceBar) {
        aiElements.confidenceBar.style.width = `${confidence}%`;
        aiElements.confidenceBar.classList.toggle('bg-red-500', isFlood);
        aiElements.confidenceBar.classList.toggle('bg-emerald-500', !isFlood);
        aiElements.confidenceBar.classList.toggle('bg-cyan-500', false);
    }

    if (aiElements.detectionTimestamp) {
        aiElements.detectionTimestamp.textContent = timestamp;
    }

    updateAlert(status, confidence);
    addHistoryItem(status, confidence, timestamp);
};

const captureFrameBlob = () => new Promise((resolve, reject) => {
    const video = aiElements.video;
    const canvas = aiElements.canvas;

    if (!video || !canvas || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
        reject(new Error('Video frame is not ready yet'));
        return;
    }

    canvas.width = video.videoWidth || 1280;
    canvas.height = video.videoHeight || 720;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob((blob) => {
        if (!blob) {
            reject(new Error('Failed to encode video frame'));
            return;
        }

        resolve(blob);
    }, 'image/jpeg', 0.82);
});

const sendFrameForDetection = async () => {
    if (!isDetecting || isProcessing) {
        return;
    }

    isProcessing = true;
    setProcessingState(true);
    setEngineConnection(true, 'Processing');

    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), AI_REQUEST_TIMEOUT_MS);

    try {
        const blob = await captureFrameBlob();
        const formData = new FormData();
        formData.append('image', blob, 'cctv-frame.jpg');
        formData.append('file', blob, 'cctv-frame.jpg');

        const response = await fetch(endpoint(), {
            method: 'POST',
            body: formData,
            cache: 'no-store',
            signal: controller.signal,
        });

        if (!response.ok) {
            throw new Error(`YOLO AI Engine responded with ${response.status}`);
        }

        const payload = await response.json();
        updateDetectionResult(normalizeStatus(payload));
        setEngineConnection(true);
    } catch (error) {
        setEngineConnection(false);
        console.warn('AI CCTV detection failed:', error);
    } finally {
        window.clearTimeout(timeout);
        isProcessing = false;
        setProcessingState(false);
    }
};

const startDetection = async () => {
    if (isDetecting || !hasAiDashboard()) {
        return;
    }

    isDetecting = true;
    setButtons();

    try {
        await aiElements.video.play();
    } catch (error) {
        console.warn('Video autoplay failed:', error);
    }

    setEngineConnection(true, 'Starting');
    sendFrameForDetection();
    aiDetectionTimer = window.setInterval(sendFrameForDetection, DETECTION_INTERVAL_MS);
};

const stopDetection = () => {
    isDetecting = false;
    window.clearInterval(aiDetectionTimer);
    aiDetectionTimer = null;
    setButtons();
    setProcessingState(false);
    setEngineConnection(true, 'Standby');

    if (aiElements.videoBadge) {
        aiElements.videoBadge.textContent = 'Detection stopped';
    }
};

const changeVideoSource = () => {
    if (!aiElements.video || !aiElements.source) {
        return;
    }

    const wasDetecting = isDetecting;

    if (wasDetecting) {
        stopDetection();
    }

    aiElements.video.src = aiElements.source.value;
    aiElements.video.load();

    if (aiElements.videoBadge) {
        aiElements.videoBadge.textContent = 'Video source loaded';
    }
};

const initAiCctvDetection = () => {
    queryAiElements();

    if (!hasAiDashboard()) {
        return;
    }

    aiElements.startButton.addEventListener('click', startDetection);
    aiElements.stopButton.addEventListener('click', stopDetection);
    aiElements.source?.addEventListener('change', changeVideoSource);
    aiElements.video?.addEventListener('error', () => {
        if (aiElements.videoBadge) {
            aiElements.videoBadge.textContent = 'Video placeholder not found';
        }
    });
    setButtons();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAiCctvDetection);
} else {
    initAiCctvDetection();
}
