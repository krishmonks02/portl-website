jQuery(document).ready(function ($) {

    // on click view btn on all reviews page
    $('.view-more-btn').on('click', function () {
        const data = $(this).data('details');

        // Prepare preview HTML for existing images
        let mediaPreviewHTML = '';
        if (Array.isArray(data.uploaded_image_poster)) {
            mediaPreviewHTML = data.uploaded_image_poster.map(url => {
                let preview = url.match(/\.(jpg|jpeg|png|webp)$/i)
                    ? `<img src="${url}" style="width:60px; border-radius:4px;" />`
                    : `View File`;

                return `
                    <div class="media-item-image" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                        <button type="button" class="delete-media delete-media-image" data-url="${url}"
                            style="position:absolute; top:-6px; right:-6px; background:red; color:#fff; border:none;
                                border-radius:50%; width:18px; height:18px; font-size:12px; cursor:pointer; padding:0px; z-index:10;">
                            &times;
                        </button>
                        <div style="text-align:center;">
                            ${preview}
                            <div><a href="${url}" type="button" class="button preview-btn" target="_blank" style="font-size:12px; display:block;">Preview</a></div>
                        </div>
                    </div>`;
            }).join('');
        }

        // Prepare preview HTML for existing videos
        let videoPreviewHtml = '';
        if (Array.isArray(data.uploaded_video_file)) {
            videoPreviewHtml = data.uploaded_video_file.map(url => {
                let preview = url.match(/\.(mp4|webm|ogg)$/i)
                    ? `<video src="${url}" width="80" muted style="border-radius:4px;">Your browser does not support the video tag</video>`
                    : `View File`;

                return `
                    <div class="media-item-video" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                        <button type="button" class="delete-media delete-media-video" data-url="${url}"
                            style="position:absolute; top:-6px; right:-6px; background:red; color:#fff; border:none;
                                border-radius:50%; width:18px; height:18px; font-size:12px; cursor:pointer; padding:0px; z-index:10;">
                            &times;
                        </button>
                        <div style="text-align:center;">
                            ${preview}
                            <div><a href="${url}" type="button" class="button preview-btn" target="_blank" style="font-size:12px; display:block;">Preview</a></div>
                        </div>
                    </div>`;
            }).join('');
        }

        // Initialize arrays for tracking media
        let existingImages = Array.isArray(data.uploaded_image_poster) ? [...data.uploaded_image_poster] : [];
        let existingVideos = Array.isArray(data.uploaded_video_file) ? [...data.uploaded_video_file] : [];
        let newImages = [];
        let newVideos = [];
        let filesToDelete = [];

        const modalContent = `
            <button type="button" class="button close-modal" style="float:right;">X</button>
            <h2>Edit Review</h2>
            <form id="edit-review-form" class="edit-review-form" style="margin-top:40px; margin-bottom:20px" enctype="multipart/form-data">
                <input type="hidden" name="review_id" value="${data.id}" />
                <p><strong>Product:</strong> ${data.product_title}</p>

                <p>
                    <label><strong>Customer Name:<span class="text-danger">*</span></strong><br>
                        <input type="text" name="customer_name" value="${data.customer_name}" required />
                    </label>
                </p>

                <p>
                    <label><strong>Email:<span class="text-danger">*</span></strong><br>
                        <input type="hidden" name="customer_email" value="${data.customer_email}" required/>
                        <input type="email" name="show-only-mail" value="${data.customer_email}" disabled/>
                    </label>
                </p>

                <p>
                    <label><strong>Ratings:<span class="text-danger">*</span></strong><br>
                        <select name="ratings" id="ratings" required>
                            <option value="">Select Rating</option>
                            ${[...Array(10)].map((_, i) => {
                                const val = (i + 1) * 0.5;
                                return `<option value="${val}" ${data.ratings == val ? 'selected' : ''}>${val}</option>`;
                            }).join('')}
                        </select>
                    </label>
                </p>

                <p>
                    <label><strong>Review Description:<span class="text-danger">*</span></strong><br>
                        <textarea name="review_description" required>${data.review_description}</textarea>
                    </label>
                </p>

                <p>
                <label><strong>Source:<span class="text-danger">*</span></strong><br>
                        <select name="review_source" id="review_source" required>
                            <option value="" disabled>Select Source</option>
                            <option value="instagram" ${data.review_source == 'instagram' ? 'selected' : ''}>Instagram</option>
                            <option value="linkedin" ${data.review_source == 'linkedin' ? 'selected' : ''}>LinkedIn</option>
                            <option value="google" ${data.review_source == 'google' ? 'selected' : ''}>Google</option>
                            <option value="facebook" ${data.review_source == 'facebook' ? 'selected' : ''}>Facebook</option>
                            <option value="twitter" ${data.review_source == 'twitter' ? 'selected' : ''}>Twitter</option>
                            <option value="others" ${data.review_source == 'others' ? 'selected' : ''}>Others</option>
                        </select>
                    </label>
                </p>

                <p>
                    <label><strong>Review Link:</strong><br>
                        <input type="url" name="review_link" value="${data.review_link || ''}"/>
                    </label>
                </p>

                <p>
                    <label><strong>Upload Image/Poster:</strong><br>
                        <input type="file" name="mediaImage[]" id="mediaImageInput" accept=".jpeg, .jpg, .png, .webp" multiple />
                        <input type="hidden" name="existing_images_json" id="existing_images_json" value='${JSON.stringify(existingImages)}' />
                    </label>
                </p>
                <div id="image-preview-container" style="margin-bottom: 15px;">${mediaPreviewHTML}</div>

                <p>
                    <label><strong>Upload Video:</strong><br>
                        <input type="file" name="mediaVideo[]" id="mediaVideoInput" accept="video/mp4,video/webm,video/ogg" multiple />
                        <input type="hidden" name="existing_videos_json" id="existing_videos_json" value='${JSON.stringify(existingVideos)}' />
                    </label>
                </p>
                <div id="video-preview-container" style="margin-bottom: 15px;">${videoPreviewHtml}</div>

                <input type="hidden" name="files_to_delete_json" id="files_to_delete_json" value="[]" />

                <div id="ajax-loader" style="display:none; text-align:left; padding:3px; margin-top:20px">
                    <span class="spinner" style="display:inline-block; width:20px; height:20px; border:2px solid #ccc; border-top:2px solid #000; border-radius:50%; animation: spin 1s linear infinite;"></span>
                    <span style="margin-left: 10px; font-weight:700; color:#912bd5">Saving review, please wait...</span>
                </div>
                <div style="margin-top: 15px;">
                    <button type="submit" class="button button-primary">Update</button>
                    <button type="button" class="button close-modal">Cancel</button>
                </div>
                <p id="error-mssg-body"></p>
            </form>
        `;

        $('#review-modal .modal-body').html(modalContent);
        $('#review-modal').fadeIn();

        // Delete media image
        $('#review-modal').off('click', '.delete-media-image').on('click', '.delete-media-image', function () {
            const urlToRemove = $(this).data('url');
            const nameToRemove = $(this).data('name');
            // If exists in existingImages, remove from there and add to filesToDelete
            if(existingImages.includes(urlToRemove)) {
                existingImages = existingImages.filter(url => url !== urlToRemove);
                filesToDelete.push(urlToRemove);
                $('#existing_images_json').val(JSON.stringify(existingImages));
            } else {
                // It’s a new file preview, remove from newImages
                newImages = newImages.filter(file => file.fileName !== nameToRemove);

            }
            $(this).closest('.media-item-image').remove();
            $('#files_to_delete_json').val(JSON.stringify(filesToDelete));
        });

        // Delete media video
        $('#review-modal').off('click', '.delete-media-video').on('click', '.delete-media-video', function () {
            const urlToRemove = $(this).data('url');
            const nameToRemove = $(this).data('name');
            if(existingVideos.includes(urlToRemove)) {
                existingVideos = existingVideos.filter(url => url !== urlToRemove);
                filesToDelete.push(urlToRemove);
                $('#existing_videos_json').val(JSON.stringify(existingVideos));
            } else {
                newVideos = newVideos.filter(file => file.fileName !== nameToRemove);
            }
            $(this).closest('.media-item-video').remove();
            $('#files_to_delete_json').val(JSON.stringify(filesToDelete));
        });

        // Close modal
        $('#review-modal').off('click', '.close-modal').on('click', '.close-modal', function () {
            $('#review-modal').fadeOut().find('.modal-body').html('');
        });

        // Handle image input change - local preview only
        $('#review-modal').off('change', '#mediaImageInput').on('change', '#mediaImageInput', function () {
            const files = this.files;
            if (!files.length) return;

            const container = $('#image-preview-container');

            // Calculate total after adding these files
            if(existingImages.length + newImages.length + files.length > 1) {
                alert('Maximum 1 image allowed including existing');
                this.value = '';
                return;
            }

            for(let file of files) {
                // Validate file type and size
                const err = validateFile(file, 'image');
                if(err) {
                    alert(err);
                    continue;
                }

                // Create a local preview URL
                const url = URL.createObjectURL(file);
                const fileName = `${file.name}_${Date.now()}`;
                newImages.push({ file, url, fileName });

                const previewHTML = `
                    <div class="media-item-image" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                        <button type="button" class="delete-media delete-media-image" data-url="${url}" data-name="${fileName}"
                            style="position:absolute; top:-6px; right:-6px; background:red; color:#fff; border:none;
                                border-radius:50%; width:18px; height:18px; font-size:12px; cursor:pointer; padding:0px; z-index:10;">
                            &times;
                        </button>
                        <div style="text-align:center;">
                            <img src="${url}" style="width:60px; border-radius:4px;" />
                            <div><a href="${url}" target="_blank" class="button preview-btn" style="font-size:12px; display:block;">Preview</a></div>
                        </div>
                    </div>
                `;
                container.append(previewHTML);
            }

            this.value = ''; // reset input
        });

        // Handle video input change - local preview only
        $('#review-modal').off('change', '#mediaVideoInput').on('change', '#mediaVideoInput', function () {
            const files = this.files;
            if (!files.length) return;

            const container = $('#video-preview-container');

            // Max 1 video including existing
            if(existingVideos.length + newVideos.length + files.length > 1) {
                alert('Maximum 1 video allowed including existing');
                this.value = '';
                return;
            }

            for(let file of files) {
                // Validate file type and size
                const err = validateFile(file, 'video');
                if(err) {
                    alert(err);
                    continue;
                }

                const url = URL.createObjectURL(file);
                const fileName = `${file.name}_${Date.now()}`;
                newVideos.push({ file, url, fileName });

                const previewHTML = `
                    <div class="media-item-video" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                        <button type="button" class="delete-media delete-media-video" data-url="${url}" data-name="${fileName}"
                            style="position:absolute; top:-6px; right:-6px; background:red; color:#fff; border:none;
                                border-radius:50%; width:18px; height:18px; font-size:12px; cursor:pointer; padding:0px; z-index:10;">
                            &times;
                        </button>
                        <div style="text-align:center;">
                            <video src="${url}" width="80" muted style="border-radius:4px;">Your browser does not support the video tag</video>
                            <div><a href="${url}" target="_blank" class="button preview-btn" style="font-size:12px; display:block;">Preview</a></div>
                        </div>
                    </div>
                `;
                container.append(previewHTML);
            }

            this.value = ''; // reset input
        });

        // Handle form submit - upload new files + send existing + deleted
        $('#review-modal').off('submit', '#edit-review-form').on('submit', '#edit-review-form', function (e) {
            e.preventDefault();

            $('#error-mssg-body').empty();
            $('#ajax-loader').show();

            const form = this;
            const formData = new FormData(form);

            for (let item of newImages) {
                if (item.file instanceof File && item.file.name && item.file.size > 0) {
                    formData.append('mediaImage[]', item.file);
                }
            }

            for (let item of newVideos) {
                if (item.file instanceof File && item.file.name && item.file.size > 0) {
                    formData.append('mediaVideo[]', item.file);
                }
            }


            // Append existing media as JSON strings
            formData.set('existing_images_json', JSON.stringify(existingImages));
            formData.set('existing_videos_json', JSON.stringify(existingVideos));

            // Append deleted files
            formData.set('files_to_delete_json', JSON.stringify(filesToDelete));

            formData.set('action', 'update_review_details');

            if (!confirm('Are you sure you want to update this review?')){
                $('#ajax-loader').hide();
                return;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function (res) {
                    $('#ajax-loader').hide();
                    if(res.success === true){
                        alert(res.data.message || 'Updated successfully!');
                        $('#review-modal').fadeOut().find('.modal-body').html('');
                        location.reload();
                    } else {
                        alert(res.data.message || 'Failed to update successfully!');
                        $('#error-mssg-body').append('<span class="error-mssg">'+res.data.message+'</span>');
                    }
                },
                error: function () {
                    $('#ajax-loader').hide();
                    alert('Something went wrong while saving.');
                }
            });
        });
    });


    // ==== review approval ajax ====
    $('.approve-review-btn').on('click', function() {
        const data = $(this).data('details');

        // preview with remove button and preview button
        let mediaPreviewHTML = '';
        if (Array.isArray(data.uploaded_image_poster)) {
            mediaPreviewHTML = data.uploaded_image_poster.map((url, index) => {
                let preview = '';
                if (url.match(/\.(jpg|jpeg|png|webp)$/i)) {
                    preview = `<img src="${url}" style="width:60px; border-radius:4px;" />`;
                }else {
                    preview = `View File`;
                }

                return `
                    <div class="media-item" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                        <div style="text-align:center;">
                            ${preview}
                            <div><a href="${url}" type="button" class="button preview-btn" target="_blank" style="font-size:12px; display:block;">Preview</a></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // preview with remove button and preview button
        let videoPreviewHTML = '';
        if (Array.isArray(data.uploaded_video_file)) {
            videoPreviewHTML = data.uploaded_video_file.map((url, index) => {
                let preview = '';
                if (url.match(/\.(mp4|webm|ogg)$/i)) {
                    preview = `<video src="${url}" width="80" muted style="border-radius:4px;">Your browser does not support the video tag</video>`;
                } else {
                    preview = `View File`;
                }

                return `
                    <div class="media-item" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                        <div style="text-align:center;">
                            ${preview}
                            <div><a href="${url}" type="button" class="button preview-btn" target="_blank" style="font-size:12px; display:block;">Preview</a></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        const modalContent = `
            <div>
                <h2>Review Details</h2>
                <p><strong>Product:</strong> ${data.product_title}</p>
                <p><strong>Customer Name:</strong> ${data.customer_name}</p>
                <p><strong>Customer Email:</strong> ${data.customer_email}</p>
                <p><strong>Rating:</strong> ${data.ratings} ⭐</p>
                <p><strong>Description:</strong> ${data.review_description}</p>
                <p><strong>Author:</strong> ${data.added_from}</p>
                <p><strong>Date:</strong> ${data.added_at}</p>
                <p><strong>Author Id: </strong> ${data.added_by}</p>
                <div style="margin-bottom: 15px;">
                    <p><strong>Uploaded image / Poster: </strong></p>
                    ${mediaPreviewHTML}
                </div>
                <div style="margin-bottom: 15px;">
                    <p><strong>Uploaded Video: </strong></p>
                    ${videoPreviewHTML}
                </div>
                <form id="approve-review-form" class="approve-review-form">
                    <input type="hidden" name="review_id" value="${data.id}" />
                    <div id="ajax-loader" style="display:none; text-align:left; padding:3px; margin-top:20px">
                        <span class="spinner" style="display:inline-block; width:20px; height:20px; border:2px solid #ccc; border-top:2px solid #000; border-radius:50%; animation: spin 1s linear infinite;"></span>
                        <span style="margin-left: 10px; font-weight:700; color:#912bd5">Saving review, please wait...</span>
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="submit" class="button button-primary">Approve Now</button>
                        <button type="button" class="button close-modal">Cancel</button>
                    </div>
                    <p id="error-mssg-body"></p>
                </form
                    
            </div>
        `;

        $('#approve-review-modal .modal-body').html(modalContent);
        $('#approve-review-modal').fadeIn();

        $('#approve-review-modal').on('click', '.close-modal', function () {
            $('#approve-review-modal').fadeOut().find('.modal-body').html('');
        });

        // Form submit logic
        $('#approve-review-form').on('submit', function (e) {
            e.preventDefault();

            $('#error-mssg-body').empty();

            // Show loader before sending
            $('#ajax-loader').show();

            const formData = new FormData(this);
            formData.append('action', 'approve_individual_review');

            if (!confirm('Are you sure you want to approve this review?')){
                $('#ajax-loader').hide();
                return;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function (res) {
                    if(res.success == true){
                        alert(res.message || 'Review approved successfully!');
                        $('#ajax-loader').hide(); // Hide loader
                        $('#review-modal').fadeOut().find('.modal-body').html('');
                        location.reload();
                    }else{
                        alert(res.data.message || 'Failed to approve!');
                        $('#ajax-loader').hide(); // Hide loader
                        $('#error-mssg-body').append('<span class="error-mssg">'+res.data.message+'</span>');
                    }
                },
                error: function () {
                    $('#ajax-loader').hide();
                    alert('Something went wrong while saving.');
                }
            });
        });
    });

    // delete review ajax
    $(document).on('click', '.delete-review-btn', function () {
        const reviewId = $(this).data('review-id');

        if (!confirm('Are you sure you want to delete this review?')) return;

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'delete_individual_review',
                review_id: reviewId
            },
            success: function (res) {
                if (res.success) {
                    alert(res.data.message);
                    // remove the row from table or reload
                    // $(`.delete-review-btn[data-review-id="${reviewId}"]`).closest('tr').fadeOut();
                    location.reload();
                } else {
                    alert(res.data.message || 'Something went wrong.');
                }
            },
            error: function () {
                alert('Error while deleting.');
            }
        });
    });

    // Utility: Validate file type & size
    function validateFile(file, type) {
        const imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        const videoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        const maxImageSize = 1 * 1024 * 1024; // 1MB
        const maxVideoSize = 7 * 1024 * 1024; // 7MB

        if(type === 'image') {
            if(!imageTypes.includes(file.type)) return 'Invalid image file type';
            if(file.size > maxImageSize) return 'Image file size exceeds 1MB';
        } else if(type === 'video') {
            if(!videoTypes.includes(file.type)) return 'Invalid video file type';
            if(file.size > maxVideoSize) return 'Video file size exceeds 7MB';
        }
        return null;
    }

    // helps to delete media file url but it will directly remove image from folder.
    function deleteMediaFileFromServer(fileUrl) {
        fetch('/wp-json/custom/v1/delete-media-file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce // Required if using nonce
            },
            body: JSON.stringify({ file_url: fileUrl })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                console.log('File deleted from server');
                return true
            } else {
                console.warn('File deletion failed:', res.message || res);
                return false
            }
        })
        .catch(err => {
            console.error('Delete request failed:', err);
            return false
        });
    }

});
