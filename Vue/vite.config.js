import path from 'path'
import { resolve } from 'path'
import {fileURLToPath, URL} from 'url'

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import { viteStaticCopy } from 'vite-plugin-static-copy'

const pathSrc = path.resolve(__dirname, './')

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        viteStaticCopy({
            targets: [
                {
                    src: '../Resources/assets/',
                    dest: '../../../../../../public/vaahcms/modules/vaahstore/',
                }
            ]
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./', import.meta.url))
        }
    },

    build: {
        chunkSizeWarningLimit: 3000,
        target: "esnext",
        outDir: '../Resources/assets/build/',
        rollupOptions: {
            output: {
                entryFileNames: `[name].js`,
                chunkFileNames: `[name].js`,
                assetFileNames: `[name].[ext]`
            },
        }
    },
    server: {
        watch: { usePolling: true, },
        port: 8464,
        hmr:{
            protocol: 'ws',
            host: 'localhost',
        }
    }
})
