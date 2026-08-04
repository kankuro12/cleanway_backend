// Client-side image → WebP conversion (canvas). Falls back to the original
// file when the browser can't decode it (e.g. HEIC).
function convertToWebp(file) {
    return new Promise(function (resolve) {
        if (!file || !file.type || file.type.indexOf('image/') !== 0 || typeof Image === 'undefined') {
            resolve(file);
            return;
        }
        var url = URL.createObjectURL(file);
        var img = new Image();
        img.onload = function () {
            try {
                var maxDim = 1920;
                var scale = Math.min(1, maxDim / Math.max(img.width, img.height));
                var canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(img.width * scale));
                canvas.height = Math.max(1, Math.round(img.height * scale));
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function (blob) {
                    URL.revokeObjectURL(url);
                    if (blob) {
                        resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.webp', { type: 'image/webp' }));
                    } else {
                        resolve(file);
                    }
                }, 'image/webp', 0.82);
            } catch (e) {
                URL.revokeObjectURL(url);
                resolve(file);
            }
        };
        img.onerror = function () {
            URL.revokeObjectURL(url);
            resolve(file);
        };
        img.src = url;
    });
}
