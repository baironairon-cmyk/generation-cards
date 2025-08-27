import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add logging and timing for all API requests
window.axios.interceptors.request.use(
    (config) => {
        try {
            config.__startTimeMs = Date.now();
            const method = (config.method || 'GET').toUpperCase();
            const url = config.url || '';
            // Log request details
            console.log('[API REQUEST]', method, url, {
                params: config.params,
                data: config.data,
                headers: config.headers,
            });
        } catch (e) {
            // no-op
        }
        return config;
    },
    (error) => {
        console.log('[API REQUEST ERROR before send]', error);
        return Promise.reject(error);
    }
);

window.axios.interceptors.response.use(
    (response) => {
        try {
            const started = response.config.__startTimeMs || Date.now();
            const durationMs = Date.now() - started;
            const method = (response.config.method || 'GET').toUpperCase();
            const url = response.config.url || '';
            console.log('[API RESPONSE]', method, url, '->', response.status, `in ${durationMs}ms`, response.data);
        } catch (e) {
            // no-op
        }
        return response;
    },
    (error) => {
        try {
            const cfg = error.config || {};
            const started = cfg.__startTimeMs || Date.now();
            const durationMs = Date.now() - started;
            const method = (cfg.method || 'GET').toUpperCase();
            const url = cfg.url || '';
            const status = error.response ? error.response.status : 'NO_RESPONSE';
            console.log('[API ERROR]', method, url, '->', status, `in ${durationMs}ms`, error.response?.data || error.message);
        } catch (e) {
            console.log('[API ERROR]', error);
        }
        return Promise.reject(error);
    }
);
