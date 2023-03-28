import {defineConfig} from "vite"
import {svelte} from "@sveltejs/vite-plugin-svelte"

export default defineConfig({
	plugins: [
		svelte({
			configFile: '../tools/svelte.config.js',
		})
	],
	root: "ui",
	css: {
		postcss: __dirname + '/postcss.config.cjs',
	},
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
