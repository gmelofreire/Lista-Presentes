import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    // Defina VITE_HMR_HOST (ex.: dominio do ngrok) para expor o dev server via https.
    const tunnelHost = env.VITE_HMR_HOST;

    return {
    server: {
        host: tunnelHost ? true : '127.0.0.1',
        ...(tunnelHost
            ? { https: true, hmr: { protocol: 'wss', host: tunnelHost } }
            : {}),
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
};
});
