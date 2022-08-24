import { defineConfig } from "vite"
import { svelte } from "@sveltejs/vite-plugin-svelte"

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        svelte({
			configFile: '../tools/svelte.config.js',
		})
    ],
	root: "ui",
    build: {
        rollupOptions: {
            input: "ui/src/main.ts",
            output: {
                entryFileNames: `assets/[name].js`,
                chunkFileNames: `assets/[name].js`,
                assetFileNames: `assets/[name].[ext]`
            }
        },
    }
})
