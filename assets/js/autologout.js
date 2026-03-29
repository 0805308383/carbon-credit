// Auto logout after 1 minute of inactivity
let idleTime = 0;

function timerIncrement() {
    idleTime++;
    if (idleTime > 60) { // 1 minute (60 seconds)
        window.location.href = '../auth/logout.php';
    }
}

document.addEventListener('mousemove', function () {
    idleTime = 0;
});

document.addEventListener('keypress', function () {
    idleTime = 0;
});

// Check every second
setInterval(timerIncrement, 1000);
