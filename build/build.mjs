import { rm, mkdir, copyFile } from "fs/promises";
import { dirname, resolve, relative, basename } from "path";
import { fileURLToPath } from "url";
import { build as esbuild, context as esbuildContext } from "esbuild";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const rootDir = resolve(__dirname, "..");

const args = process.argv.slice(2);
const isWatch = args.includes("--watch");
const distDir = resolve(rootDir, "dist");
const distJsDir = resolve(distDir, "js");

async function clean() {
  await rm(distDir, { recursive: true, force: true });
}

async function ensureOutputDirs() {
  await mkdir(distJsDir, { recursive: true });
}

async function copyStaticAssets() {
  const files = [
    {
      from: resolve(rootDir, "assets/js/iconify-icon.min.js"),
      to: resolve(distJsDir, "iconify-icon.min.js"),
    },
  ];

  await Promise.all(
    files.map(async ({ from, to }) => {
      try {
        await copyFile(from, to);
        console.log(`Kopyalandı: ${basename(from)} → ${relative(rootDir, to)}`);
      } catch (error) {
        if (error.code === "ENOENT") {
          console.warn(`Warning: ${from} bulunamadı, atlandı.`);
        } else {
          throw error;
        }
      }
    })
  );
}

const buildOptions = {
  entryPoints: [resolve(rootDir, "assets/js/main.js")],
  bundle: true,
  outfile: resolve(distJsDir, "main.js"),
  minify: !isWatch,
  sourcemap: true,
  target: "es2019",
  logLevel: "info",
};

async function runBuild() {
  await clean();
  await ensureOutputDirs();

  if (isWatch) {
    const ctx = await esbuildContext({
      ...buildOptions,
      plugins: [
        {
          name: "copy-static-assets",
          setup(build) {
            build.onEnd(async () => {
              try {
                await copyStaticAssets();
              } catch (error) {
                console.error("Statik dosyalar kopyalanamadı:", error);
              }
            });
          },
        },
      ],
    });

    await ctx.watch();
    console.log("Watch modu açık. Değişiklikler izleniyor...");
  } else {
    await esbuild(buildOptions);
    await copyStaticAssets();
    console.log("Build tamamlandı.");
  }
}

runBuild().catch((error) => {
  console.error(error);
  process.exit(1);
});
