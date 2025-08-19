// Dont bother analyzing this code, this is not part of the challenge :D

class MikuChat {
    constructor() {
        this.mikuText = document.getElementById('mikuText');
        this.typingIndicator = document.getElementById('typingIndicator');
        this.responseButtons = document.getElementById('responseButtons');
        this.userInput = document.getElementById('userInput');
        this.yesBtn = document.getElementById('yesBtn');
        this.noBtn = document.getElementById('noBtn');
        this.sendBtn = document.getElementById('sendBtn');
        this.userResponse = document.getElementById('userResponse');
        
        this.currentDialogueIndex = 0;
        this.isTyping = false;
        
        this.dialogues = [
            {
                text: "Hi there! I'm Hatsune Miku! 🎵",
                type: "message",
                delay: 2000
            },
            {
                text: "How are you feeling today?",
                type: "input",
                delay: 1500
            },
            {
                text: "That's wonderful to hear! ✨",
                type: "message",
                delay: 2000
            },
            {
                text: "I have something special for you today...",
                type: "message",
                delay: 2500
            },
            {
                text: "Would you like to receive a special flag? 🏁",
                type: "flag_question",
                delay: 2000
            }
        ];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        setTimeout(() => this.startDialogue(), 1000);
    }
    
    bindEvents() {
        this.yesBtn.addEventListener('click', () => this.handleYesClick());
        this.noBtn.addEventListener('click', () => this.handleNoClick());
        this.sendBtn.addEventListener('click', () => this.handleUserInput());
        this.userResponse.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.handleUserInput();
        });
        
        this.yesBtn.addEventListener('mouseenter', () => this.handleYesHover());
    }
    
    async startDialogue() {
        if (this.currentDialogueIndex < this.dialogues.length) {
            const dialogue = this.dialogues[this.currentDialogueIndex];
            await this.typeMessage(dialogue.text);
            
            setTimeout(() => {
                this.handleDialogueType(dialogue.type);
                this.currentDialogueIndex++;
            }, dialogue.delay);
        }
    }
    
    handleDialogueType(type) {
        this.hideAllInputs();
        
        switch(type) {
            case 'input':
                this.showUserInput();
                break;
            case 'flag_question':
                this.showFlagButtons();
                break;
            case 'message':
                setTimeout(() => this.startDialogue(), 1000);
                break;
        }
    }
    
    async typeMessage(message) {
        if (this.isTyping) return;
        
        this.isTyping = true;
        this.showTypingIndicator();
        
        // Simulate typing delay
        await this.delay(1000);
        
        this.hideTypingIndicator();
        this.mikuText.textContent = '';
        
        for (let i = 0; i < message.length; i++) {
            this.mikuText.textContent += message[i];
            await this.delay(50);
        }
        
        this.isTyping = false;
    }
    
    showTypingIndicator() {
        this.typingIndicator.style.display = 'flex';
        this.mikuText.style.display = 'none';
    }
    
    hideTypingIndicator() {
        this.typingIndicator.style.display = 'none';
        this.mikuText.style.display = 'block';
    }
    
    showUserInput() {
        this.userInput.style.display = 'flex';
        this.userResponse.focus();
    }
    
    showFlagButtons() {
        this.responseButtons.style.display = 'flex';
    }
    
    hideAllInputs() {
        this.userInput.style.display = 'none';
        this.responseButtons.style.display = 'none';
    }
    
    async handleUserInput() {
        const userText = this.userResponse.value.trim();
        if (!userText) return;
        
        this.userResponse.value = '';
        this.hideAllInputs();
        
        setTimeout(() => this.startDialogue(), 500);
    }
    
    handleYesClick() {
        console.log("User clicked Yes successfully!");
    }
    
    handleYesHover() {
        this.moveButtonsRandomly();
    }
    
    async handleNoClick() {
        this.hideAllInputs();
        await this.typeMessage("Good luck with your journey! Maybe next time~ 🎵");
        
        setTimeout(() => {
            this.typeMessage("Thanks for chatting with me! ( ﾟ∀ﾟ)ｱﾊ");
        }, 2500);
    }
    
    moveButtonsRandomly() {
        const buttonsContainer = this.responseButtons;
        
        const containerRect = buttonsContainer.getBoundingClientRect();
        
        const maxX = window.innerWidth - containerRect.width - 20;
        const maxY = window.innerHeight - containerRect.height - 20;
        const minX = 450;
        const minY = 20;
        
        const newX = Math.random() * (maxX - minX) + minX;
        const newY = Math.random() * (maxY - minY) + minY;
        
        buttonsContainer.classList.add('moving-buttons');
        buttonsContainer.style.position = 'fixed';
        
        requestAnimationFrame(() => {
            buttonsContainer.style.left = newX + 'px';
            buttonsContainer.style.top = newY + 'px';
        });
        
        const funMessages = [
            "Not so fast! 😜",
            "Try to catch me! 🏃‍♀️",
            "Missed me! ✨",
            "I'm too quick for you! 💨",
            "Keep trying! 🎯",
            "Almost there! 😅",
            "Nope, try again! 🎪",
            "You can't catch me! 🦋",
            "Too slow! ⚡",
            "Nice try! 🎭"
        ];
        
        const randomMessage = funMessages[Math.floor(Math.random() * funMessages.length)];
        
        this.mikuText.textContent = randomMessage;
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new MikuChat();
}); 