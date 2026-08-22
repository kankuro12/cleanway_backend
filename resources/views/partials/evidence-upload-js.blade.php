@include('partials.webp-convert')
(function ($) {
    var pendingFiles = {}; // type => [File]
    var uploading = {};    // type => bool
    var canDeleteEvidence = {{ auth()->check() && in_array(auth()->user()?->role, ['admin', 'supervisor'], true) ? 'true' : 'false' }};

    $(document).on('click', '.ev-tab-btn', function (e) {
        e.preventDefault();
        $('.ev-tab-btn').removeClass('active');
        $(this).addClass('active');
        var type = $(this).data('type');
        $('.tab-pane[id^="ev-"]').hide().removeClass('show active');
        $('#ev-' + type).fadeIn(150).addClass('show active');
    });

    function renderPreviews(type) {
        var $grid = $('.ev-preview-grid[data-type="' + type + '"]');
        $grid.empty();

        var files = pendingFiles[type] || [];
        files.forEach(function (file, idx) {
            var url = URL.createObjectURL(file);
            var $item = $('<div class="position-relative d-inline-block me-1 mb-1 draft-preview-item" data-index="' + idx + '">' +
                '<img class="rounded border shadow-sm" style="width:84px;height:84px;object-fit:cover;" src="' + url + '" alt="' + $('<div>').text(file.name).html() + '">' +
                '<button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 rounded-circle btn-remove-draft" ' +
                'data-type="' + type + '" data-index="' + idx + '" ' +
                'style="width:22px;height:22px;line-height:1;transform:translate(30%, -30%); shadow: 0 2px 6px rgba(0,0,0,0.25);" title="Remove image">' +
                '<i class="bi bi-x" aria-hidden="true"></i>' +
                '</button>' +
                '</div>');
            $grid.append($item);
        });

        var n = files.length;
        var $btn = $('.ev-upload[data-type="' + type + '"]');
        if (n > 0) {
            $btn.removeClass('d-none').prop('disabled', false).html(
                '<i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>Upload ' + n + ' Photo' + (n > 1 ? 's' : '')
            );
        } else {
            $btn.addClass('d-none').prop('disabled', true);
            $('.ev-msg[data-type="' + type + '"]').text('');
        }
    }

    // 1. Remove draft image before uploading
    $(document).on('click', '.btn-remove-draft', function (e) {
        e.preventDefault();
        var type = $(this).data('type');
        var index = parseInt($(this).data('index'), 10);
        if (pendingFiles[type] && pendingFiles[type][index] !== undefined) {
            pendingFiles[type].splice(index, 1);
            renderPreviews(type);
        }
    });

    // 2. Upload files via Axios
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
                    var deleteBtnHtml = '';
                    if (canDeleteEvidence) {
                        var deleteUrl = '{{ route('tasks.evidence.delete', [$task, 999999]) }}'.replace('999999', res.data.id);
                        deleteBtnHtml = '<button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 rounded-circle btn-delete-uploaded-evidence" ' +
                            'style="width:22px;height:22px;line-height:1;transform:translate(30%, -30%); shadow: 0 2px 6px rgba(0,0,0,0.25);" ' +
                            'data-url="' + deleteUrl + '" title="Delete photo"><i class="bi bi-x" aria-hidden="true"></i></button>';
                    }

                    var filename = res.data.original_filename || file.name || 'Evidence Photo';
                    var photoItemHtml = '<div class="position-relative text-center me-2 mb-2 ev-photo-item" data-evidence-id="' + res.data.id + '" title="' + filename + '">' +
                        '<img class="rounded border shadow-sm" style="width:88px;height:88px;object-fit:cover;cursor:pointer;" src="' + res.data.view_url + '" alt="' + filename + '" data-ev-lightbox>' +
                        deleteBtnHtml +
                        '<div class="extra-small text-secondary text-truncate mt-1" style="max-width:88px;">' + filename + '</div></div>';

                    $('.ev-photos[data-type="' + type + '"]').append(photoItemHtml);
                    if (type !== 'all') {
                        $('.ev-photos[data-type="all"]').append(photoItemHtml);
                        var $allCount = $('#count-all');
                        if ($allCount.length) {
                            $allCount.text(parseInt($allCount.text() || '0', 10) + 1);
                        }
                    }
                })
                .catch(function (err) {
                    failed++;
                    $msg.text(err.response?.data?.message || 'Upload failed.');
                })
                .finally(next);
        }

        next();
    }

    // 3. Delete uploaded evidence photo (Admin & Supervisor only)
    $(document).on('click', '.btn-delete-uploaded-evidence', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');
        var $item = $btn.closest('.ev-photo-item');
        var $pane = $btn.closest('.tab-pane');
        var type = $pane.find('.ev-file').data('type');

        if (!confirm('Delete this evidence photo permanently?')) return;

        axios.delete(url)
            .then(function (res) {
                $item.fadeOut(200, function () {
                    $(this).remove();
                    var $count = $('#count-' + type);
                    if ($count.length) {
                        var current = parseInt($count.text() || '0', 10);
                        if (current > 0) $count.text(current - 1);
                    }
                });
            })
            .catch(function (err) {
                alert(err.response?.data?.message || 'Error deleting evidence photo.');
            });
    });

    function handleEvidenceFiles(type, originals, captured, input) {
        Promise.all(originals.map(convertToWebp)).then(function (converted) {
            pendingFiles[type] = (pendingFiles[type] || []).concat(converted);
            renderPreviews(type);
            var saved = (converted[0].size / 1024).toFixed(0) + ' KB';
            $('.ev-msg[data-type="' + type + '"]').text(
                converted.length + ' photo(s) selected (~' + saved + ' each) — click upload to attach.'
            );
            input.value = '';
            if (captured) {
                uploadFiles(type, pendingFiles[type].splice(0));
            }
        });
    }

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
