<?php
/**
 * Lyric Image Download Functionality
 *
 * This file handles the creation and download of lyric images
 * with canvas-based rendering for Instagram-ready images
 *
 * @package Gufte
 * @since 1.5.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add lyric image download JavaScript to wp_head
 * This function outputs the JavaScript needed for creating and downloading lyric images
 */
function gufte_add_lyric_image_download_script() {
    if (is_singular()) {
        echo '<script>
function createAndDownloadLyricImage(lineId, text, language, singer, songTitle) {
    // Canvas oluştur (veya mevcut olanı al)
    let canvas = document.getElementById("lyricImageCanvas");
    if (!canvas) {
        canvas = document.createElement("canvas");
        canvas.id = "lyricImageCanvas";
        document.body.appendChild(canvas);
    }

    // Canvas boyutlarını Instagram uygun 4:5 oranına ayarla
    const width = 900;
    const height = 1080; // 4:5 oranına yakın
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext("2d");

    // Arka planı temizle ve gradient uygula
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    gradient.addColorStop(0, "#ffffff");
    gradient.addColorStop(1, "#f0f9ff");
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, width, height);

    // Üst kısma zarif bir dekoratif çizgi ekle - rengi mor/pembe olarak değiştirdik
    const lineGradient = ctx.createLinearGradient(0, 0, width, 0);
    lineGradient.addColorStop(0, "#c025d3");
    lineGradient.addColorStop(0.5, "#d14fde");
    lineGradient.addColorStop(1, "#e28eec");
    ctx.fillStyle = lineGradient;
    ctx.fillRect(0, 0, width, 10);

    // Site logosu veya marka imzası
    ctx.fillStyle = "rgba(0, 0, 0, 0.1)";
    ctx.font = "bold 24px Arial, sans-serif";
    ctx.textAlign = "center";
    ctx.fillText("arcuras.com", width / 2, height - 80);

    // Şarkı başlığını ekle (varsa)
    if (songTitle) {
        ctx.fillStyle = "#0f172a";
        ctx.font = "bold 36px Arial, sans-serif";
        ctx.textAlign = "center";

        // Uzun başlık kontrolü
        if (ctx.measureText(songTitle).width > width - 100) {
            // Başlığı kısaltma
            let shortTitle = songTitle;
            while (ctx.measureText(shortTitle + "...").width > width - 100 && shortTitle.length > 0) {
                shortTitle = shortTitle.slice(0, -1);
            }
            ctx.fillText(shortTitle + "...", width/2, 100);
        } else {
            ctx.fillText(songTitle, width/2, 100);
        }
    }

// And replace it with:
// Şarkıcı adını ekle (varsa) - Birden fazla şarkıcı desteği
if (singer) {
    ctx.fillStyle = "#475569";
    ctx.font = "normal 28px Arial, sans-serif";
    ctx.textAlign = "center";

    // Şarkıcı isminin uzunluğunu kontrol et
    if (ctx.measureText(singer).width > width - 100) {
        // Birden fazla şarkıcı varsa ve çok uzunsa, kısalt
        let shortSinger = singer;
        while (ctx.measureText(shortSinger + "...").width > width - 100 && shortSinger.length > 0) {
            shortSinger = shortSinger.slice(0, -1);
        }
        ctx.fillText(shortSinger + "...", width/2, 140);
    } else {
        ctx.fillText(singer, width/2, 140);
    }
}

// Featured Imageı ekle (eğer varsa)
const featuredImage = document.querySelector(".post-thumbnail img");
if (featuredImage) {
    // Görseli çizmek için hazırlık
    const imgSize = 200; // Görsel boyutu
    const imgX = (width - imgSize) / 2; // Ortalanmış X pozisyonu
    const imgY = 170; // Başlık ve şarkıcı adının altında

    ctx.save(); // Mevcut çizim durumunu kaydet

    // Kare (yuvarlatılmış köşeli) kırpma alanı oluştur
    const borderRadius = 10; // Kenarların yuvarlatılma miktarı (px)
    ctx.beginPath();
    ctx.moveTo(imgX + borderRadius, imgY);
    ctx.lineTo(imgX + imgSize - borderRadius, imgY);
    ctx.quadraticCurveTo(imgX + imgSize, imgY, imgX + imgSize, imgY + borderRadius);
    ctx.lineTo(imgX + imgSize, imgY + imgSize - borderRadius);
    ctx.quadraticCurveTo(imgX + imgSize, imgY + imgSize, imgX + imgSize - borderRadius, imgY + imgSize);
    ctx.lineTo(imgX + borderRadius, imgY + imgSize);
    ctx.quadraticCurveTo(imgX, imgY + imgSize, imgX, imgY + imgSize - borderRadius);
    ctx.lineTo(imgX, imgY + borderRadius);
    ctx.quadraticCurveTo(imgX, imgY, imgX + borderRadius, imgY);
    ctx.closePath();
    ctx.clip();

    // Görseli çiz
    try {
        ctx.drawImage(featuredImage, imgX, imgY, imgSize, imgSize);
    } catch (e) {
        console.error("Image could not be drawn:", e);
    }

    // Çerçeve ekle (aynı path üzerinden)
    ctx.strokeStyle = "rgba(192, 37, 211, 0.4)";
    ctx.lineWidth = 4;
    ctx.stroke();

    ctx.restore(); // Önceki çizim durumuna dön
}


    // Ana metin ve çevirisi için metin çiftini al
    const lineElement = document.getElementById(lineId);
    if (!lineElement) return;

    // Orijinal metni al (her zaman lyric-main-text içindedir)
    let originalText = "";
    const originalElement = lineElement.querySelector("p.lyric-main-text");
    if (originalElement) {
        originalText = originalElement.textContent;
    }

    // Çeviri metni (varsa)
    let translationText = "";
    const translationElement = lineElement.querySelector("p.text-sm");
    if (translationElement) {
        translationText = translationElement.textContent;
    }

    // İlk dil sekmesi mi kontrol et (birinci dil sekmesinde çeviri yok)
    const isFirstTab = lineId.includes("-0-");

    // Dikey konumlandırmayı ayarla - featured imageın altına gelecek şekilde
    const verticalCenter = height / 2 + 60; // Featured image için biraz daha aşağı

    // Orijinal metin için ayarlar
    ctx.fillStyle = "#0f172a";
    ctx.font = "bold 42px Arial, sans-serif";
    ctx.textAlign = "center";

    // Orijinal metni çiz
    const linesDrawn = wrapText(
        ctx,
        originalText,
        width/2,
        verticalCenter - (translationText && !isFirstTab ? 100 : 0),
        width - 150,
        60
    );

    // Eğer çeviri metni varsa ve ilk sekme değilse, çeviriyi göster
    if (translationText && !isFirstTab) {
        // Çeviri metni için ayarlar
        ctx.fillStyle = "#64748b"; // Daha soluk gri renk
        ctx.font = "italic 32px Arial, sans-serif";

        // Çeviri metnini altına çiz
        wrapText(
            ctx,
            translationText,
            width/2,
            verticalCenter + 100 + ((linesDrawn - 1) * 60),
            width - 150,
            45
        );
    }

    // Müzik notaları veya dekoratif elemanlar ekle
    drawDecorativeElements(ctx, width, height);

    // Canvası PNG olarak indir
    const link = document.createElement("a");

    // Dosya adını oluştur - şarkıcı VE şarkı adını ekle
    let filename = "lyrics";
    if (songTitle) {
        filename += "-" + songTitle.toLowerCase().replace(/[^a-z0-9]/g, "").substring(0, 30);
    }
    if (singer) {
        filename += "-" + singer.toLowerCase().replace(/[^a-z0-9]/g, "").substring(0, 20);
    }
    filename += ".png";

    link.download = filename;
    link.href = canvas.toDataURL("image/png");

    // Double click/download sorununu önlemek için kodu çağıran işlevi yalnızca bir kez çalıştır
    if (window.lyricDownloadTimeout) {
        clearTimeout(window.lyricDownloadTimeout);
    }

    window.lyricDownloadTimeout = setTimeout(function() {
        link.click();
        window.lyricDownloadTimeout = null;
    }, 100);
}

// Metni çok satırlı olarak çiz
function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
    // Metin uzunluğunu kontrol et, çok uzunsa ortaya yerleştir
    const textLength = text.length;
    const baseY = textLength > 100 ? y - 100 : y;

    const words = text.split(" ");
    let line = "";
    let testLine = "";
    let lineCount = 0;

    for (let n = 0; n < words.length; n++) {
        testLine = line + words[n] + " ";
        const metrics = ctx.measureText(testLine);
        const testWidth = metrics.width;

        if (testWidth > maxWidth && n > 0) {
            ctx.fillText(line, x, baseY + (lineCount * lineHeight));
            line = words[n] + " ";
            lineCount++;
        } else {
            line = testLine;
        }
    }

    // Son satırı çiz
    ctx.fillText(line, x, baseY + (lineCount * lineHeight));
    return lineCount + 1; // Toplam satır sayısını döndür
}

// Dekoratif elemanlar çiz
function drawDecorativeElements(ctx, width, height) {
    // Mor/pembe rengi kullan - #c025d3
    ctx.fillStyle = "rgba(192, 37, 211, 0.1)"; // #c025d3 ile %10 opaklık

    // Müzik notası tarzı bir şekil çiz
    const noteX = 100;
    const noteY = height - 160;

    // Nota kafası
    ctx.beginPath();
    ctx.ellipse(noteX, noteY, 20, 15, Math.PI / 4, 0, Math.PI * 2);
    ctx.fill();

    // Nota sapı
    ctx.fillRect(noteX + 15, noteY - 15, 3, -50);

    // Nota bağlantısı
    ctx.beginPath();
    ctx.ellipse(noteX + 26, noteY - 60, 12, 8, Math.PI / 4, 0, Math.PI * 2);
    ctx.fill();

    // Sağ üst köşeye bir başka müzik notası
    const note2X = width - 100;
    const note2Y = 200;

    // Nota kafası
    ctx.beginPath();
    ctx.ellipse(note2X, note2Y, 15, 12, Math.PI / 3, 0, Math.PI * 2);
    ctx.fill();

    // Nota sapı
    ctx.fillRect(note2X - 15, note2Y - 10, 3, -40);

    // Rastgele noktalar - mor renkte
    ctx.fillStyle = "rgba(192, 37, 211, 0.05)"; // #c025d3 ile %5 opaklık
    for (let i = 0; i < 50; i++) {
        const x = Math.random() * width;
        const y = Math.random() * (height - 100);
        const size = Math.random() * 5 + 1;
        ctx.beginPath();
        ctx.arc(x, y, size, 0, Math.PI * 2);
        ctx.fill();
    }

    // Alt kısma dekoratif bir çizgi - mor gradient
    const bottomGradient = ctx.createLinearGradient(0, height - 60, width, height - 60);
    bottomGradient.addColorStop(0, "rgba(192, 37, 211, 0.2)"); // #c025d3
    bottomGradient.addColorStop(0.5, "rgba(209, 79, 222, 0.2)"); // #d14fde
    bottomGradient.addColorStop(1, "rgba(226, 142, 236, 0.2)"); // #e28eec
    ctx.fillStyle = bottomGradient;
    ctx.fillRect(0, height - 60, width, 10);

    // Köşelere ince vurgu çizgileri - mor renkte
    ctx.fillStyle = "rgba(192, 37, 211, 0.03)"; // #c025d3 ile %3 opaklık
    ctx.fillRect(40, 40, 80, 2);
    ctx.fillRect(40, 40, 2, 80);
    ctx.fillRect(width - 120, 40, 80, 2);
    ctx.fillRect(width - 40, 40, 2, 80);
    ctx.fillRect(40, height - 142, 80, 2);
    ctx.fillRect(40, height - 220, 2, 80);
    ctx.fillRect(width - 120, height - 142, 80, 2);
    ctx.fillRect(width - 40, height - 220, 2, 80);

    // Dikey alanı tamamlamak için ek dekoratif öğeler - mor renkte
    ctx.fillRect(40, height/2 - 1, 40, 2);
    ctx.fillRect(width - 80, height/2 - 1, 40, 2);
}

document.addEventListener("DOMContentLoaded", function() {
    // İndirme butonları
    var downloadButtons = document.querySelectorAll(".download-lyric-btn");
    if (downloadButtons) {
        for (var i = 0; i < downloadButtons.length; i++) {
            downloadButtons[i].addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                createAndDownloadLyricImage(
                    this.getAttribute("data-line-id"),
                    this.getAttribute("data-text"),
                    this.getAttribute("data-lang"),
                    this.getAttribute("data-singer"),
                    this.getAttribute("data-title")
                );
            });
        }
    }

    // Kopyalama butonları
    var copyButtons = document.querySelectorAll(".copy-lyric-btn");
    if (copyButtons) {
        for (var j = 0; j < copyButtons.length; j++) {
            copyButtons[j].addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                var text = this.getAttribute("data-text");
                var button = this;

                // Kopyalama fonksiyonu
                try {
                    // Modern yöntem
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text)
                            .then(function() {
                                // Başarılı
                                var iconElement = button.querySelector(".iconify");
                                iconElement.setAttribute("data-icon", "mdi:check");
                                button.classList.add("text-green-600");
                                button.classList.remove("text-primary-600");

                                setTimeout(function() {
                                    iconElement.setAttribute("data-icon", "mdi:content-copy");
                                    button.classList.remove("text-green-600");
                                    button.classList.add("text-primary-600");
                                }, 1500);
                            })
                            .catch(function() {
                                alert("Kopyalama işlemi başarısız oldu");
                            });
                    } else {
                        // Alternatif yöntem
                        var textArea = document.createElement("textarea");
                        textArea.value = text;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand("copy");
                        document.body.removeChild(textArea);

                        // Başarılı
                        var iconElement = button.querySelector(".iconify");
                        iconElement.setAttribute("data-icon", "mdi:check");
                        button.classList.add("text-green-600");
                        button.classList.remove("text-primary-600");

                        setTimeout(function() {
                            iconElement.setAttribute("data-icon", "mdi:content-copy");
                            button.classList.remove("text-green-600");
                            button.classList.add("text-primary-600");
                        }, 1500);
                    }
                } catch (err) {
                    console.error("Kopyalama hatası:", err);
                }
            });
        }
    }
});
        </script>';
    }
}
add_action('wp_head', 'gufte_add_lyric_image_download_script');
