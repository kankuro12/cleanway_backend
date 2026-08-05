@include('partials.webp-convert')
(function ($) {
    var pendingFiles = {}; // type => [File]
    var uploading = {};    // type => bool

    function renderPreviews(type) {
        var $grid = $('.ev-preview-grid[data-type="' + type + '"]');
        $grid.empty();
        (pendingFiles[type] || []).forEach(function (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('<img class="rounded border" style="width:84px;height:84px;object-fit:cover;" alt="' + file.name + '">')
                    .attr('src', e.target.result)
                    .appendTo($grid);
            };
            reader.readAsDataURL(file);
        });
        var n = (pendingFiles[type] || []).length;
        $('.ev-upload[data-type="' + type + '"]').prop('disabled', n === 0).html(
            '<i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>Upload' + (n > 1 ? ' (' + n + ')' : '')
        );
    }

    function uploadFiles(type, files) {
        if (!files.length || uploading[type]) return;
        uploading[type] = true;
        var $btn = $('.ev-upload[data-type="' + type + '"]');
        var $msg = $('.ev-msg[data-type="' + type + '"]');
        $btn.prop('disabled', true);
        $msg.text('Uploading ' + files.length + ' photo(s)…');

        var done = 0, failed = 0;

        function next() {
            if (!files.length) {
                uploading[type] = false;
                pendingFiles[type] = [];
                $('.ev-preview-grid[data-type="' + type + '"]').empty();
                renderPreviews(type);
                $msg.text(done + ' uploaded' + (failed ? ', ' + failed + ' failed' : '') + '.');
                var $count = $('#count-' + type);
                $count.text(parseInt($count.text() || '0', 10) + done);
                return;
            }
            var file = files.shift();
            var fd = new FormData();
            fd.append('evidence', file);
            fd.append('evidence_type', type);

            axios.post('{{ route('tasks.evidence', $task) }}', fd)
                .then(function (res) {
                    done++;
                    $('.ev-photos[data-type="' + type + '"]').append(
                        '<div class="text-center"><img class="rounded border" style="width:84px;height:84px;object-fit:cover;" ' +
                        'src="' + res.data.view_url + '" alt=""><div class="small text-muted">processing</div></div>'
                    );
                })
                .catch(function (err) {
                    failed++;
                    $msg.text(err.response?.data?.message || 'Upload failed.');
                })
                .finally(next);
        }

        next();
    }

    function handleEvidenceFiles(type, originals, captured, input) {
        // Convert every image to WebP (canvas), fall back to original on failure.
        Promise.all(originals.map(convertToWebp)).then(function (converted) {
            pendingFiles[type] = (pendingFiles[type] || []).concat(converted);
            renderPreviews(type);
            var saved = (converted[0].size / 1024).toFixed(0) + ' KB';
            $('.ev-msg[data-type="' + type + '"]').text(
                converted.length + ' photo(s) selected (webp, ~' + saved + ' each) — upload to attach.'
            );
            input.value = '';
            // Camera captures upload immediately — no extra click.
            if (captured) {
                uploadFiles(type, pendingFiles[type].splice(0));
            }
        });
    }

    // Some Android builds fire change with an empty file list — retry once.
    $(document).on('change', '.ev-file, .ev-capture', function () {
        var type = $(this).data('type');
        var captured = $(this).hasClass('ev-capture');
        var originals = Array.from(this.files || []);
        var self = this;

        if (!originals.length) {
            setTimeout(function () {
                var retry = Array.from(self.files || []);
                if (retry.length) {
                    handleEvidenceFiles(type, retry, captured, self);
                }
            }, 300);
            return;
        }

        handleEvidenceFiles(type, originals, captured, this);
    });

    $(document).on('click', '.ev-upload', function () {
        var type = $(this).data('type');
        uploadFiles(type, (pendingFiles[type] || []).splice(0));
    });
})(jQuery);

// Lightbox: browse all photos of the clicked category, wraps at the ends.
(function ($) {
    var $modal = $('#evLightbox');
    var photos = [];
    var index = 0;

    function show(i) {
        if (!photos.length) return;
        index = (i + photos.length) % photos.length;
        var img = photos[index];
        $('#evLbCaption').text((index + 1) + ' / ' + photos.length + (img.alt ? ' — ' + img.alt : ''));
        renderThumbs();
        var $img = $('#evLbImg');
        $img.stop(true).fadeTo(120, 0.15, function () {
            $img.attr('src', img.src).attr('alt', img.alt || 'Evidence photo');
            $img.fadeTo(150, 1);
        });
    }

    function renderThumbs() {
        var $strip = $('.ev-lb-thumbs').empty();
        photos.forEach(function (img, i) {
            $('<img>').attr('src', img.src).attr('alt', '')
                .addClass('ev-lb-thumb' + (i === index ? ' active' : ''))
                .on('click', function () { show(i); })
                .appendTo($strip);
        });
        var $active = $strip.find('.active');
        if ($active.length) $active[0].scrollIntoView({ inline: 'nearest', block: 'nearest' });
    }

    $(document).on('click', '.ev-photos img[data-ev-lightbox]', function () {
        photos = $(this).closest('.ev-photos').find('img[data-ev-lightbox]').toArray();
        show(photos.indexOf(this));
        $modal.modal('show');
    });

    $modal.on('click', '.ev-lb-prev', function () { show(index - 1); });
    $modal.on('click', '.ev-lb-next', function () { show(index + 1); });

    // Touch swipe on the photo: left/right navigates.
    var touchX = null;
    $modal.on('touchstart', '.modal-body', function (e) {
        touchX = e.originalEvent.touches[0].clientX;
    });
    $modal.on('touchend', '.modal-body', function (e) {
        if (touchX === null) return;
        var dx = e.originalEvent.changedTouches[0].clientX - touchX;
        touchX = null;
        if (Math.abs(dx) < 40) return;
        show(index + (dx < 0 ? 1 : -1));
    });

    $(document).on('keydown', function (e) {
        if (!$modal.hasClass('show')) return;
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });
})(jQuery);
