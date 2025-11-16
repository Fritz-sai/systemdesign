document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('nav');
    if (navToggle && nav) {
        navToggle.addEventListener('click', () => {
            nav.classList.toggle('open');
        });
        document.addEventListener('click', (event) => {
            if (!nav.contains(event.target) && !navToggle.contains(event.target)) {
                nav.classList.remove('open');
            }
        });
    }

    const chatbot = document.getElementById('chatbot');
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotClose = document.getElementById('chatbot-close');

    if (chatbot && chatbotToggle) {
        chatbotToggle.addEventListener('click', () => {
            chatbot.style.display = 'flex';
            chatbotToggle.style.display = 'none';
        });
    }

    if (chatbot && chatbotClose) {
        chatbotClose.addEventListener('click', () => {
            chatbot.style.display = 'none';
            chatbotToggle.style.display = 'inline-flex';
        });
    }

    const fadeElements = document.querySelectorAll('.hero, .features article, .product-card, .service-card, .card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.2
    });

    fadeElements.forEach((el) => {
        el.classList.add('fade-in');
        observer.observe(el);
    });

    const chatbotBody = document.querySelector('.chatbot-body');
    const chatChoices = document.getElementById('chat-choices');
    
    if (chatbotBody && chatChoices) {
        // Conversation flow configuration
        const conversationFlow = {
            initial: {
                message: 'Hello! 👋 I\'m here to help. What can I assist you with today?',
                choices: [
                    { text: 'Book a Repair', value: 'repair', icon: '🔧' },
                    { text: 'Browse Products', value: 'products', icon: '🛍️' },
                    { text: 'Check Services', value: 'services', icon: '⚙️' },
                    { text: 'Contact Info', value: 'contact', icon: '📞' }
                ]
            },
            repair: {
                message: 'Great! I can help you book a repair. What issue are you experiencing?',
                choices: [
                    { text: 'Screen Broken/Cracked', value: 'repair_screen', icon: '📱' },
                    { text: 'Battery Issues', value: 'repair_battery', icon: '🔋' },
                    { text: 'Water Damage', value: 'repair_water', icon: '💧' },
                    { text: 'Charging Port', value: 'repair_charging', icon: '🔌' },
                    { text: 'Other Issue', value: 'repair_other', icon: '❓' },
                    { text: '← Back', value: 'back_initial', icon: '←' }
                ]
            },
            repair_screen: {
                message: 'Screen repair is one of our most common services! 💪 Our certified technicians use premium glass replacements. You can book a screen repair appointment now.',
                choices: [
                    { text: 'Book Screen Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: 'View Pricing', value: 'view_pricing_screen', icon: '💰' },
                    { text: '← Back', value: 'back_repair', icon: '←' }
                ]
            },
            repair_battery: {
                message: 'Battery issues are frustrating! 🔋 We offer genuine battery replacements that restore your phone\'s battery life. Most battery replacements take about 1-2 hours.',
                choices: [
                    { text: 'Book Battery Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: 'View Pricing', value: 'view_pricing_battery', icon: '💰' },
                    { text: '← Back', value: 'back_repair', icon: '←' }
                ]
            },
            repair_water: {
                message: 'Water damage needs immediate attention! 💧 Our technicians use ultrasonic cleaning and advanced diagnostics to revive water-damaged devices. Book as soon as possible for best results.',
                choices: [
                    { text: 'Book Water Damage Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: 'Emergency? Call Now', value: 'contact', icon: '📞' },
                    { text: '← Back', value: 'back_repair', icon: '←' }
                ]
            },
            repair_charging: {
                message: 'Charging port issues? 🔌 We can fix loose or unresponsive charging ports and restore fast charging capabilities.',
                choices: [
                    { text: 'Book Charging Port Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: 'View Pricing', value: 'view_pricing_charging', icon: '💰' },
                    { text: '← Back', value: 'back_repair', icon: '←' }
                ]
            },
            repair_other: {
                message: 'No problem! We handle various phone issues. 📱 You can book a repair and describe your specific issue in the booking form. Our technicians will assess and fix it.',
                choices: [
                    { text: 'Book Repair (General)', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: 'View All Services', value: 'services', icon: '⚙️' },
                    { text: '← Back', value: 'back_repair', icon: '←' }
                ]
            },
            products: {
                message: 'Explore our accessories! 🛍️ We have premium cases, screen protectors, chargers, and more.',
                choices: [
                    { text: 'View All Products', value: 'action_shop', link: 'shop.php', icon: '🛒' },
                    { text: 'Screen Protectors', value: 'products_screen', icon: '📱' },
                    { text: 'Phone Cases', value: 'products_cases', icon: '💼' },
                    { text: 'Chargers & Cables', value: 'products_chargers', icon: '🔌' },
                    { text: '← Back', value: 'back_initial', icon: '←' }
                ]
            },
            products_screen: {
                message: 'Screen protectors keep your device safe! 📱 We offer premium tempered glass protectors that maintain crystal-clear clarity while protecting against scratches and cracks.',
                choices: [
                    { text: 'Shop Screen Protectors', value: 'action_shop', link: 'shop.php', icon: '🛒' },
                    { text: '← Back', value: 'back_products', icon: '←' }
                ]
            },
            products_cases: {
                message: 'Protect your phone in style! 💼 Our cases combine durability with sleek design. Choose from various styles and materials.',
                choices: [
                    { text: 'Shop Phone Cases', value: 'action_shop', link: 'shop.php', icon: '🛒' },
                    { text: '← Back', value: 'back_products', icon: '←' }
                ]
            },
            products_chargers: {
                message: 'Fast and reliable charging solutions! 🔌 We stock high-quality chargers and cables that support fast charging and are built to last.',
                choices: [
                    { text: 'Shop Chargers & Cables', value: 'action_shop', link: 'shop.php', icon: '🛒' },
                    { text: '← Back', value: 'back_products', icon: '←' }
                ]
            },
            services: {
                message: 'Here are our main services! ⚙️',
                choices: [
                    { text: 'Screen Replacement', value: 'service_screen', icon: '📱' },
                    { text: 'Battery Replacement', value: 'service_battery', icon: '🔋' },
                    { text: 'Water Damage Treatment', value: 'service_water', icon: '💧' },
                    { text: 'View All Services', value: 'action_services', link: 'services.php', icon: '📋' },
                    { text: '← Back', value: 'back_initial', icon: '←' }
                ]
            },
            service_screen: {
                message: 'Screen Replacement: Premium glass with original feel. Most repairs completed in under 2 hours. Starting at $129.99.',
                choices: [
                    { text: 'Book Screen Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: '← Back', value: 'back_services', icon: '←' }
                ]
            },
            service_battery: {
                message: 'Battery Replacement: Genuine batteries that restore performance. Quick 1-2 hour service. Starting at $89.99.',
                choices: [
                    { text: 'Book Battery Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: '← Back', value: 'back_services', icon: '←' }
                ]
            },
            service_water: {
                message: 'Water Damage Treatment: Advanced diagnostics and ultrasonic cleaning. Starting at $149.99.',
                choices: [
                    { text: 'Book Water Damage Repair', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: '← Back', value: 'back_services', icon: '←' }
                ]
            },
            contact: {
                message: 'Need to reach us? 📞\n\n📍 Visit us: Store Location\n📧 Email: reboot@gmail.com\n☎️ Phone: 09663978744\n\n⏰ Hours:\nMonday - Saturday: 9 AM - 7 PM\nSunday: 10 AM - 5 PM',
                choices: [
                    { text: 'Visit Contact Page', value: 'action_contact', link: 'contact.php', icon: '📧' },
                    { text: '← Back', value: 'back_initial', icon: '←' }
                ]
            },
            view_pricing_screen: {
                message: 'Screen Replacement Pricing:\n\n📱 Premium Glass: $129.99\n📱 OEM Quality: $149.99\n📱 Same-Day Service: Available\n\nMost repairs completed in under 2 hours!',
                choices: [
                    { text: 'Book Now', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: '← Back', value: 'repair_screen', icon: '←' }
                ]
            },
            view_pricing_battery: {
                message: 'Battery Replacement Pricing:\n\n🔋 Standard Battery: $89.99\n🔋 Extended Life: $109.99\n⏱️ Service Time: 1-2 hours',
                choices: [
                    { text: 'Book Now', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: '← Back', value: 'repair_battery', icon: '←' }
                ]
            },
            view_pricing_charging: {
                message: 'Charging Port Repair Pricing:\n\n🔌 Standard Repair: $79.99\n🔌 Fast Charging Restore: Included\n⏱️ Service Time: 1-2 hours',
                choices: [
                    { text: 'Book Now', value: 'action_booking', link: 'booking.php', icon: '📅' },
                    { text: '← Back', value: 'repair_charging', icon: '←' }
                ]
            }
        };

        // Handle back navigation
        const backMap = {
            'back_initial': 'initial',
            'back_repair': 'repair',
            'back_products': 'products',
            'back_services': 'services'
        };

        let currentStep = 'initial';

        function showStep(step) {
            currentStep = step;
            const stepData = conversationFlow[step];
            
            if (!stepData) {
                // Handle action steps
                if (step.startsWith('action_')) {
                    const actionMap = {
                        'action_booking': 'booking.php',
                        'action_shop': 'shop.php',
                        'action_services': 'services.php',
                        'action_contact': 'contact.php'
                    };
                    if (actionMap[step]) {
                        window.location.href = actionMap[step];
                    }
                }
                return;
            }

            // Add bot message (skip if it's the initial step - message is already in HTML)
            if (step !== 'initial' && stepData.message) {
                addBotMessage(stepData.message);
            }

            // Clear and add choices
            chatChoices.innerHTML = '';
            stepData.choices.forEach(choice => {
                const button = document.createElement('button');
                button.className = 'chat-choice-btn';
                button.innerHTML = `<span class="choice-icon">${choice.icon || '•'}</span> ${choice.text}`;
                button.addEventListener('click', () => {
                    if (choice.link) {
                        // Direct link to page
                        window.location.href = choice.link;
                    } else if (backMap[choice.value]) {
                        // Handle back navigation
                        showStep(backMap[choice.value]);
                    } else {
                        // Navigate to conversation step
                        addUserMessage(choice.text);
                        setTimeout(() => {
                            showStep(choice.value);
                        }, 500);
                    }
                });
                chatChoices.appendChild(button);
            });

            scrollToBottom();
        }

        function addBotMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message bot-message';
            messageDiv.innerHTML = `<div class="message-content">${formatMessage(message)}</div>`;
            chatbotBody.insertBefore(messageDiv, chatChoices);
            scrollToBottom();
        }

        function addUserMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message user-message';
            messageDiv.innerHTML = `<div class="message-content">${message}</div>`;
            chatbotBody.insertBefore(messageDiv, chatChoices);
            scrollToBottom();
        }

        function formatMessage(text) {
            // Convert line breaks to <br>
            return text.replace(/\n/g, '<br>');
        }

        function scrollToBottom() {
            setTimeout(() => {
                chatbotBody.scrollTop = chatbotBody.scrollHeight;
            }, 100);
        }

        // Don't show initial step again if it's already shown
        // The initial message is already in the HTML
        // Just show the initial choices
        const initialData = conversationFlow.initial;
        chatChoices.innerHTML = '';
        initialData.choices.forEach(choice => {
            const button = document.createElement('button');
            button.className = 'chat-choice-btn';
            button.innerHTML = `<span class="choice-icon">${choice.icon || '•'}</span> ${choice.text}`;
            button.addEventListener('click', () => {
                if (choice.link) {
                    window.location.href = choice.link;
                } else if (backMap[choice.value]) {
                    showStep(backMap[choice.value]);
                } else {
                    addUserMessage(choice.text);
                    setTimeout(() => {
                        showStep(choice.value);
                    }, 500);
                }
            });
            chatChoices.appendChild(button);
        });
    }
});


