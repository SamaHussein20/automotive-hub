<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Welcome</title>

<style>
body{
    margin:0;
    overflow:hidden;
    background:#000;
}

video{
    width:100vw;
    height:100vh;
    object-fit:cover;
}

.skip-btn{
    position:absolute;
    top:20px;
    right:20px;
    padding:12px 25px;
    border:none;
    border-radius:30px;
    cursor:pointer;
    font-weight:bold;
}
</style>

</head>
<body>

<button class="skip-btn" onclick="goNext()">Skip</button>

<video id="introVideo" autoplay muted playsinline controls>
    <source src="video/intro.mp4" type="video/mp4">
</video>
<script>
function goNext(){
   window.location.href = "recommendation.php";
}

document.getElementById("introVideo").addEventListener("error", function(){
    goNext();
});

document.getElementById("introVideo").onended = function(){
    goNext();
};
</script>

</body>
</html>