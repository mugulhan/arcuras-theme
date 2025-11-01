import { readFile, rm, cp, mkdir } from "fs/promises";
import { execSync } from "child_process";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const themeDir = resolve(__dirname, "..");
const themesDir = resolve(themeDir, "..");

// style.css'den versiyon oku
async function getThemeVersion() {
  const styleCSS = await readFile(resolve(themeDir, "style.css"), "utf-8");
  const versionMatch = styleCSS.match(/Version:\s*([0-9.]+)/);
  return versionMatch ? versionMatch[1] : "1.0.0";
}

// Temayı ziple
async function createRelease() {
  const version = await getThemeVersion();
  const themeName = "arcuras";
  const zipName = `${themeName}-${version}.zip`;
  const zipPath = resolve(themesDir, zipName);
  const tempDir = resolve(themesDir, `.temp-${themeName}`);
  const tempThemeDir = resolve(tempDir, themeName);

  console.log(`📦 Tema ${version} versiyonu için zip oluşturuluyor...`);

  try {
    // Geçici dizini temizle ve oluştur
    await rm(tempDir, { recursive: true, force: true });
    await mkdir(tempThemeDir, { recursive: true });

    console.log(`📋 Dosyalar kopyalanıyor...`);

    // Tüm tema dosyalarını kopyala
    await cp(themeDir, tempThemeDir, {
      recursive: true,
      filter: (src) => {
        const rel = src.replace(themeDir, "");

        // Hariç tutulacak dosyalar ve klasörler
        if (rel.includes("node_modules")) return false;
        if (rel.includes(".DS_Store")) return false;
        if (rel.endsWith(".zip")) return false;
        if (rel.endsWith(".gitignore")) return false;
        if (rel.endsWith(".intelephense-wordpress-stubs.php")) return false;
        if (rel.includes("tailwind-cdn.css")) return false;
        if (rel.includes("tailwind-test.css")) return false;
        if (rel.includes("tailwind-debug.css")) return false;
        if (rel.includes("package-lock.json")) return false;
        if (rel.includes(".package-lock.json")) return false;

        return true;
      }
    });

    // Önceki zip'i sil
    await rm(zipPath, { force: true });

    console.log(`🗜️  Zip dosyası oluşturuluyor...`);

    // Zip oluştur
    execSync(`cd "${tempDir}" && zip -r "${zipName}" ${themeName}`, { stdio: "ignore" });
    execSync(`mv "${resolve(tempDir, zipName)}" "${zipPath}"`, { stdio: "ignore" });

    // Geçici dizini temizle
    await rm(tempDir, { recursive: true, force: true });

    console.log(`✅ Tema zip'i başarıyla oluşturuldu: ${zipName}`);
    console.log(`📍 Konum: ${zipPath}`);

    // Zip boyutunu göster
    const sizeOutput = execSync(`ls -lh "${zipPath}" | awk '{print $5}'`, { encoding: "utf-8" });
    console.log(`📊 Boyut: ${sizeOutput.trim()}`);

    // Dosya sayısını göster
    const fileCount = execSync(`unzip -l "${zipPath}" | tail -1 | awk '{print $2}'`, { encoding: "utf-8" });
    console.log(`📁 Dosya sayısı: ${fileCount.trim()} dosya`);

  } catch (error) {
    console.error("❌ Zip oluşturulurken hata:", error.message);
    await rm(tempDir, { recursive: true, force: true });
    process.exit(1);
  }
}

createRelease().catch(console.error);
