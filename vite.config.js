import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import * as Vue from 'vue';

/**
 * Statamic externals plugin - ensures addon uses the same Vue instance
 * as the Statamic CP (window.Vue) instead of bundling its own copy.
 */
function statamicExternals() {
    const RESOLVED_VIRTUAL_MODULE_ID = '\0vue-external';
    const vueExports = Object.keys(Vue).filter(key => key !== 'default' && /^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(key));

    return {
        name: 'statamic-externals',
        enforce: 'pre',

        resolveId(id) {
            if (id === 'vue') {
                return RESOLVED_VIRTUAL_MODULE_ID;
            }
            return null;
        },

        load(id) {
            if (id === RESOLVED_VIRTUAL_MODULE_ID) {
                const exportsList = vueExports.join(', ');
                return `
                    const Vue = window.Vue;
                    export default Vue;
                    export const { ${exportsList} } = Vue;
                `;
            }
            return null;
        },

        configResolved(resolvedConfig) {
            resolvedConfig.build.rollupOptions.plugins = resolvedConfig.build.rollupOptions.plugins || [];
            resolvedConfig.build.rollupOptions.plugins.push({
                name: 'statamic-externals',
                renderChunk(code) {
                    code = code.replace(
                        /import\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*,\s*(\{[^}]+\})\s+from\s+['"]vue['"];?/g,
                        'const $1 = window.Vue;\nconst $2 = window.Vue;'
                    );
                    return code.replace(
                        /import\s+(.+?)\s+from\s+['"]vue['"];?/g,
                        'const $1 = window.Vue;'
                    );
                }
            });
        }
    };
}

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'vite.hot',
            publicDirectory: 'resources/dist',
            input: [
                'resources/js/addon.js',
            ],
        }),
        statamicExternals(),
        vue(),
    ],
});
