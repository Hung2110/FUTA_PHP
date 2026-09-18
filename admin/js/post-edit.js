function previewImage(event, previewId, placeholderId) {
    if (event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById(previewId);
            if (output) {
                output.src = reader.result;
                output.style.display = 'block';
            }
            var placeholder = document.getElementById(placeholderId);
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const editorEl = document.getElementById('content-editor');
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

        const quill = new Quill('#content-editor', quillOptions);
        const form = document.querySelector('form');
        const contentInput = document.getElementById('content-input');

        if (form && contentInput) {
            form.addEventListener('submit', function(e) {
                // Trước khi submit, lấy nội dung HTML từ Quill và gán vào input ẩn
                contentInput.value = quill.root.innerHTML;
            });
        }
    }
});

