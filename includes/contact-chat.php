
<!-- SECTION HỖ TRỢ & CHATBOT -->
<section id="contact-support">
  <!-- Cụm nút liên hệ nhanh -->
  <div class="contact-buttons-wrapper" id="contactWrapper">
    <!-- Nút mở rộng (chỉ hiển thị trên Mobile) -->
    <button class="btn-contact-toggle" id="contactToggleBtn" aria-label="Mở menu liên hệ">
      <i class="fa-solid fa-phone icon-toggle icon-phone"></i>
      <i class="fa-brands fa-facebook-messenger icon-toggle icon-messenger"></i>
      <i class="fa-solid fa-envelope icon-toggle icon-mail"></i>
      
      <i class="fa-solid fa-xmark icon-toggle icon-close"></i>
    </button>
    
    <!-- Danh sách nút -->
    <div class="contact-buttons">
      <a href="tel:19006912" class="btn-contact call">
        <i class="fa-solid fa-phone"></i>
        <span>1900 6912</span>
      </a>
      <a href="mailto:futaadvertising@futa.vn" class="btn-contact mail">
        <i class="fa-solid fa-envelope"></i>
        <span>Mail</span>
      </a>
      <a href="https://m.me/231050773432578" target="_blank" class="btn-contact messenger">
        <i class="fa-brands fa-facebook-messenger"></i>
        <span>Messenger</span>
      </a>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('contactToggleBtn');
    const wrapper = document.getElementById('contactWrapper');
    
    if (toggleBtn && wrapper) {
      toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Ngăn sự kiện nổi bọt
        wrapper.classList.toggle('show');
      });

      // Nhấn ra ngoài màn hình để đóng menu
      document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target) && wrapper.classList.contains('show')) {
          wrapper.classList.remove('show');
        }
      });
    }
  });
</script>