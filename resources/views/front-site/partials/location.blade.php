<script>
  const addressEl = document.getElementById("address");

  if (!navigator.geolocation) {
    addressEl.placeholder =
      "Geolocation not supported. Please write your address here.";
  } else {
    navigator.geolocation.getCurrentPosition(
      () => {
        // Location ALLOWED
        addressEl.placeholder =
          "Please write your full address here";
      },
      () => {
        // Location BLOCKED
        addressEl.placeholder =
          "⚠️ Location blocked.\nPlease click the 🔒 lock icon in your browser address bar and set Location → Allow, or write your address below";
      }
    );
  }
</script>
