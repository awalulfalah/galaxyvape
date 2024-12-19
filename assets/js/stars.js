class Star {
    constructor() {
        this.x = Math.random() * window.innerWidth;
        this.y = Math.random() * window.innerHeight;
        this.size = Math.random() * 2;
        this.speedX = Math.random() * 3 - 1.5;
        this.speedY = Math.random() * 3 - 1.5;
        this.opacity = Math.random();
    }

    update() {
        this.x += this.speedX;
        this.y += this.speedY;
        
        if (this.x < 0) this.x = window.innerWidth;
        if (this.x > window.innerWidth) this.x = 0;
        if (this.y < 0) this.y = window.innerHeight;
        if (this.y > window.innerHeight) this.y = 0;

        this.opacity = Math.sin(Date.now() * 0.001 * this.speedX) * 0.5 + 0.5;
    }

    draw(ctx) {
        ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
    }
}

class Comet {
    constructor() {
        this.reset();
    }

    reset() {
        this.x = Math.random() * window.innerWidth;
        this.y = 0;
        this.size = Math.random() * 2 + 1;
        this.speedX = (Math.random() - 0.5) * 8;
        this.speedY = Math.random() * 15 + 5;
        this.tail = [];
        this.opacity = 1;
    }

    update() {
        this.tail.unshift({ x: this.x, y: this.y });
        if (this.tail.length > 20) this.tail.pop();

        this.x += this.speedX;
        this.y += this.speedY;

        if (this.y > window.innerHeight) this.reset();
    }

    draw(ctx) {
        ctx.beginPath();
        ctx.moveTo(this.x, this.y);
        
        for (let i = 0; i < this.tail.length; i++) {
            const point = this.tail[i];
            const opacity = 1 - (i / this.tail.length);
            ctx.strokeStyle = `rgba(255, 255, 255, ${opacity * 0.5})`;
            ctx.lineTo(point.x, point.y);
        }
        
        ctx.stroke();
        ctx.fillStyle = 'rgba(255, 255, 255, 1)';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
    }
}

function initStars() {
    const canvas = document.createElement('canvas');
    canvas.classList.add('stars');
    document.body.prepend(canvas);
    
    const ctx = canvas.getContext('2d');
    let stars = [];
    let comets = [];
    
    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    
    function init() {
        stars = Array(200).fill().map(() => new Star());
        comets = Array(3).fill().map(() => new Comet());
    }
    
    function animate() {
        ctx.fillStyle = 'rgba(10, 10, 46, 0.2)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        stars.forEach(star => {
            star.update();
            star.draw(ctx);
        });
        
        comets.forEach(comet => {
            comet.update();
            comet.draw(ctx);
        });
        
        requestAnimationFrame(animate);
    }
    
    window.addEventListener('resize', resize);
    resize();
    init();
    animate();
}

document.addEventListener('DOMContentLoaded', initStars); 