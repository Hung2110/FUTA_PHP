function previewImage(event, previewId) {
    if (event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById(previewId);
            if (output) {
                output.src = reader.result;
                output.style.display = 'block';
            }
            var placeholder = document.getElementById('image-placeholder');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
}

function previewVideo(event, previewId) {
    if (event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById(previewId);
            if (output) {
                output.src = reader.result;
                output.style.display = 'block';
            }
            var placeholder = document.getElementById('video-placeholder');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const editorEl = document.getElementById('description-editor');
    if (editorEl && window.Quill) {
        const quillOptions = {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video', 'blockquote', 'code-block'],
                    ['clean']
                ]
            }
        };

        const quill = new Quill('#description-editor', quillOptions);
        const form = document.querySelector('form');
        const descriptionInput = document.getElementById('description-input');

        if (form && descriptionInput) {
            form.addEventListener('submit', function(e) {
                // Trước khi submit, lấy nội dung HTML từ Quill và gán vào input ẩn
                descriptionInput.value = quill.root.innerHTML;
            });
        }
    }
});

