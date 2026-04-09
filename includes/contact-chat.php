
<!-- SECTION HỖ TRỢ & CHATBOT -->
<section id="contact-support">
  <!-- Nút liên hệ nhanh -->
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

  <!-- CHATBOT BUTTON -->
<!--<div class="chatbot-button" id="open-chatbot">
    <span>CHAT NOW</span>
    <i class="bi bi-chat-dots-fill"></i>
  </div>

  <!-- CHATBOT BOX -->
  <!--<div class="chatbot-box" id="chatbot-box">
    <div class="chatbot-header">
      <h5>Tư vấn trực tuyến</h5>
      <span id="close-chatbot" class="close-icon">&times;</span>
    </div>

    <div class="chatbot-body">
      <div class="intro-text">
        <b>FUTA Advertising</b> – Giải pháp quảng cáo toàn diện trên xe khách, pano, billboard và digital.  
        Vui lòng để lại thông tin, đội ngũ của chúng tôi sẽ liên hệ tư vấn sớm nhất!
      </div>
      <form class="chat-form" id="chat-form">
        <input type="text" id="name" placeholder="Họ và tên" required>
        <input type="email" id="email" placeholder="Email" required>
        <input type="tel" id="phone" placeholder="Số điện thoại" required>
        <textarea id="message" rows="3" placeholder="Nội dung cần tư vấn..." required></textarea>
        <button type="submit">Gửi thông tin</button>
      </form>
      <div class="success-message" id="success-message" style="display:none;">
        ✅ Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm nhất có thể.
      </div>
    </section>
    <script>
    // Hiển thị/ẩn chat box
    document.getElementById('open-chatbot').onclick = function() {
      document.getElementById('chatbot-box').style.display = 'block';
    };
    document.getElementById('close-chatbot').onclick = function() {
      document.getElementById('chatbot-box').style.display = 'none';
    };

    // Gửi form qua AJAX vào bảng contact
    document.getElementById('chat-form').onsubmit = function(e) {
      e.preventDefault();
      var form = this;
      var data = new FormData(form);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '/includes/contact-chat-api.php');
      xhr.onload = function() {
        if (xhr.status === 200) {
          try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
              form.style.display = 'none';
              document.getElementById('success-message').style.display = 'block';
            } else {
              alert(res.error || 'Gửi thất bại!');
            }
          } catch(e) { alert('Gửi thất bại!'); }
        } else {
          alert('Gửi thất bại!');
        }
      };
      xhr.send(data);
    };
    </script>
    </div>
  </div>
</div>-->
</section>