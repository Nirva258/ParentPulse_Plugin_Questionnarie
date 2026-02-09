// Wait for the DOM to fully load before initializing anything
document.addEventListener('DOMContentLoaded', function () {
    console.log("DOM fully loaded and parsed");

    // =============================
    // GLOBAL: Capture User Answers
    // =============================

    // --- Global Answers Capture ---
    let userAnswers = [];

    // Listen for any click on the page to track selected answers
    document.body.addEventListener("click", function(e) {
        console.log("Body click detected on element:", e.target);
        // Capture clicks on any .next-question or .final-answer buttons
        if (e.target.matches(".next-question, .final-answer")) {
            let answerText = e.target.textContent.trim();// Get button text
            userAnswers.push(answerText);// Save it to the array
            console.log("Captured answer:", answerText);
        }
    });

    // ====================================
    // QUIZ FLOW: Question Navigation Logic
    // ====================================
    // 

    let questionHistory = []; // Stack to track visited questions
    let currentQuestion = document.querySelector('.question-container'); // Initially the first question

    // Hide a question by passing in its DOM node
    function hideQuestion(question) {
        if (question) {
            question.style.display = 'none';
        }
    }
   // Show a question using its ID
    function showQuestion(questionId) {
        const nextQuestion = document.getElementById(questionId);
        if (nextQuestion) {
            nextQuestion.style.display = 'block';
            currentQuestion = nextQuestion; // Update current question
        }
    }
    // Move to the next question (called when clicking a .next-question button)
    function moveToNextQuestion(event) {
        if (currentQuestion) {
            questionHistory.push(currentQuestion.id); // Store current question before moving
            hideQuestion(currentQuestion);// Hide it
        }
        const targetId = event.target.getAttribute('data-target');// Get the ID of the next question
        showQuestion(targetId); // Show next question

        // Show back button only if there’s navigation history
        const backButton = currentQuestion.querySelector('#back-button');
        if (questionHistory.length > 0 && backButton) {
            backButton.style.display = 'block';
        }
    }
    // Move back to the previous question
    // function moveToPreviousQuestion() {
    //     if (questionHistory.length > 0) {
    //         hideQuestion(currentQuestion);// Hide current
    //         const lastQuestionId = questionHistory.pop();// Pop last from stack
    //         showQuestion(lastQuestionId);// Go back
    //         const backButton = currentQuestion.querySelector('#back-button');
    //         if (questionHistory.length === 0 && backButton) {
    //             backButton.style.display = 'none';// Hide back button if at start
    //         }
    //     }
    // }

    function moveToPreviousQuestion() {
        if (questionHistory.length > 0) {
            // Hide the currently visible question
            hideQuestion(currentQuestion);
    
            // Get ID of the last question and show it
            const lastQuestionId = questionHistory.pop();
            const previousQuestion = document.getElementById(lastQuestionId);
            showQuestion(lastQuestionId);
    
            // ✅ Update the current question pointer manually
            currentQuestion = previousQuestion;
    
            // ✅ Force-hide the subscription form every time user goes back
            const formContainer = document.getElementById("subscription-form-container");
            if (formContainer) {
                formContainer.style.display = "none";
                console.log("✅ Subscription form hidden after going back");
            }
    
            // Show/hide back button based on remaining history
            const backButton = currentQuestion.querySelector('#back-button');
            if (questionHistory.length === 0 && backButton) {
                backButton.style.display = 'none';
            }
        }
    }

 // Attach event listeners to all "next-question" buttons
    document.querySelectorAll('.next-question').forEach(button => {
        button.addEventListener('click', moveToNextQuestion);
    });
// Attach event listeners to all "back" buttons inside question containers
    document.querySelectorAll('.question-container').forEach(question => {
        const backButton = question.querySelector('#back-button');
        if (backButton) {
            backButton.addEventListener('click', moveToPreviousQuestion);
        }
    });

    // ============================
    // QUIZ START: Entry Point
    // ============================
   
   
    const startButton = document.getElementById("start-quiz-btn"); // The button to start the quiz
    const questionsContainer = document.getElementById("questions-container"); // The container holding all the questions
    // When the user clicks the start button, show the quiz
    startButton.addEventListener("click", function () {
        if (questionsContainer) {
            questionsContainer.style.display = "block"; // Reveal the questions
        }
        startButton.style.display = "none"; // Optionally hide the start button
    });

    // =========================================
    // FINAL ANSWER: Show Subscription Form
    // =========================================
    // When a final-answer is clicked, show the email form and save the redirect URL
    document.body.addEventListener("click", function (e) {
        if (e.target.matches(".final-answer")) {
            e.preventDefault();
            // Get the redirect URL stored in the button's data attribute
            var redirectUrl = e.target.getAttribute("data-url");
            console.log("Final answer clicked, redirect URL:", redirectUrl);
            // Set the hidden input's value in the subscription form with this URL
            var redirectInput = document.getElementById("redirect-url");
            if (redirectInput) {
                redirectInput.value = redirectUrl;
            }
            // Reveal the subscription form container (it should be in the DOM but hidden)
            var formContainer = document.getElementById("subscription-form-container");
            if (formContainer) {
                formContainer.style.display = "block";
                console.log("Subscription form container shown");
            }
        }
    });
    // ======================================
    // SUBSCRIPTION FORM: Mailchimp + AJAX
    // ======================================
    const subscriptionForm = document.getElementById("subscription-form");
    if (subscriptionForm) {
        subscriptionForm.addEventListener("submit", function (e) {
            e.preventDefault();// Stop regular form submit
            //Fill hidden input with all captured answers
            const answersField = document.getElementById("user-answers");
            if (answersField) {
                answersField.value = userAnswers.join(", ");
                console.log("Updated user answers:", answersField.value);
            }
            const formData = new FormData(subscriptionForm);// Create form payload

            // Use a localized AJAX URL if available; fallback to PHP echo
            const ajaxUrl = (typeof admin_ajax_url !== 'undefined' && admin_ajax_url.ajax_url)
                ? admin_ajax_url.ajax_url
                : "<?php echo admin_url('admin-ajax.php'); ?>";
            // POST to WordPress backend to handle Mailchimp subscription
            fetch(ajaxUrl + "?action=subscribe_mailchimp", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())// Expect JSON response
            .then(data => {
                if (data.success) {
                     // On success, redirect to stored URL
                    const redirectUrl = document.getElementById("redirect-url").value;
                    window.location.href = redirectUrl;
                } else {
                    alert("Subscription failed: " + data.data);
                }
            })
            .catch(err => {
                console.error("Error during subscription:", err);
                alert("An unexpected error occurred.");
            });
        });
    }
});
