<?php 

// Prevent direct access to this file for security
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueues the plugin's JavaScript and CSS files on the frontend.
 * Also localizes the script to make the WordPress admin-ajax URL available in JavaScript.
 */
function enqueue_questions_plugin_script() {
    // Enqueue the plugin's JavaScript file
    // 'questions-plugin-script' is the handle name for the script
    // plugin_dir_url(__FILE__) returns the URL path to this plugin directory
    // array() means the script has no dependencies
    // null for version means WordPress won't append a version number
    // true means the script is loaded in the footer
    wp_enqueue_script('questions-plugin-script', plugin_dir_url(__FILE__) . 'script.js', array(), null, true);

    // Enqueue the plugin's CSS file to style the quiz or form
    wp_enqueue_style('questions-plugin-style', plugin_dir_url(__FILE__) . 'style.css');
    
    // Make the AJAX URL available to JavaScript using wp_localize_script
    // This creates a JavaScript object `admin_ajax_url` with the key 'ajax_url'
    // This allows script.js to send AJAX requests to WordPress
    wp_localize_script('questions-plugin-script', 'admin_ajax_url', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
    
}
// Hook the function to load scripts and styles on the frontend
add_action('wp_enqueue_scripts', 'enqueue_questions_plugin_script');


/**
 * Renders the main content for the Parent Pulse plugin.
 *
 * This includes:
 * - A call-to-action button to start the quiz
 * - A hidden question container that displays dynamically after button click
 * - All question steps gathered via modular helper functions
 * - A styled redirect message shown after form completion
 *
 * @return string HTML output to be injected into the page
 */

///////////////Questions///////////////
function questions_plugin_render() {

    // ================================
    // Start Quiz Button (Visible by default)
    // ================================
    
    $output = '<button id="start-quiz-btn" style="padding: 12px 20px; font-size: 16px; background-color: #a2d2ff; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
    Find the Right Coach for You!!
    </button>';
    
    // ================================
    // Questions Container (Hidden initially)
    // This div will be made visible via JavaScript when user clicks the button.
    // ================================
    $output .= '<div id="questions-container" style="display: none;">';

    // Render each section of the quiz using reusable question functions.
    // These functions likely return HTML for individual questions or blocks.
    
    // Introductory questions
    $output .= question_one();// First intro question
    $output .= childquestions();// Child-related general questions
    $output .= Yes(); // Confirmatory or decision branch

    // Education-related questions
    $output .= show_learningDevelopment();// Learning development questions
    $output .= show_learningDifficulties();// Difficulties in learning
    $output .= show_motivation();// Motivation level
    $output .= show_age();// Child's age
    $output .= show_extracurriculars(); // Extracurricular activity interests
    $output .= show_curriculum(); // Curriculum preference
    $output .= show_reading();// Reading habits and challenges
    $output .= show_giftedness(); // Giftedness assessment
    $output .= show_academic();// Academic performance

     // Social and behavioral concerns
    $output .= show_friendandsocialskills();// Friends/social skills check
    $output .= show_socialage();// Social maturity by age
    $output .= show_behavioralconcerns(); // Behavioral flags
    $output .= show_behavioralage();// Age-wise behavioral checks
     // Balancing academics and life
    $output .= show_balancingextraandacademics();

    // Emotional and health-related aspects
    $output .= show_emotionalwellbeing();// Mental/emotional check-in
    $output .= show_ageforemotional();// Emotional age range
    $output .= show_healthconcerns();// Health issues
    $output .= show_healthoptions(); // Health option selections

    // Work and stress factors
    $output .= show_workquestions(); // Questions related to work-life balance
    $output .= show_yeswork(); // Work-related confirmation
    $output .= show_sourceofstress(); // Identify sources of stress
    $output .= show_wellbeing(); // Overall well-being
    $output .= show_personalwellbeing();// Deeper dive into personal wellbeing
    $output .= show_workresponsibilities();// Workload/responsibilities
    $output .= show_copingstress(); // Stress coping strategies

    // Additional custom options
    $output .= show_option1();// Extra options 1–4 (possibly optional details)
    $output .= show_option2();
    $output .= show_option3();
    $output .= show_option4();

    // Relationship and parenting dynamics
    $output .= show_relationshipquestions();// Relationship/family setup
    $output .= show_communication();// Parent-child communication
    $output .= show_yescommunication(); // Follow-up if applicable
    $output .= show_discipline();// Discipline style
    $output .= show_yesdiscipline();// Discipline details
    $output .= show_coparenting();// Co-parenting details
    $output .= show_singleparent();// Single-parent condition check
    $output .= show_grandparentsupport();// Grandparent involvement
    $output .= show_yessupport();// Support confirmation
    $output .= show_connectingwithchild();// Connection with child
    $output .= show_yesconnecting();// Follow-up on connecting
    $output .= show_newcomerparents();// Newcomer parenting struggles
    $output .= show_yesnewparenting(); // Follow-up on above

    // ================================
    // Subscription Form
    // Collects user’s name/email and subscribes to Mailchimp
    // ================================
    $output .= render_subscription_form();

    

    // Close the hidden questions container
    $output .= '</div>'; 

 // Return the final HTML markup to be rendered on the page
    return $output;
}

/**
 * Registers a WordPress shortcode for rendering the quiz.
 *
 * This function creates the [questions_plugin] shortcode,
 * which allows users to display the full quiz interface (button + questions + form)
 * anywhere on a page or post by simply inserting [questions_plugin] in the content editor.
 */
function register_questions_shortcode() {
    // Register the shortcode [questions_plugin] and link it to the questions_plugin_render() function
    add_shortcode('questions_plugin', 'questions_plugin_render');
}
// Hook the shortcode registration function to WordPress's 'init' action
// This ensures the shortcode is registered early when WordPress is initializing
add_action('init', 'register_questions_shortcode');

/**
 * Renders the subscription form shown at the end of the quiz.
 * This form collects user details and prepares data for Mailchimp subscription.
 * It's initially hidden and displayed after the quiz completion.
 *
 * @return string HTML markup for the subscription form.
 */
function render_subscription_form() {
    ob_start();
    ?>
    <div id="subscription-form-container" style="display: none; padding: 20px; background: #f9f9f9; border: 1px solid #ccc; margin-top: 20px;">
      <h3>Please subscribe — we’ll match you with the best coach!</h3>
      <form id="subscription-form">
          <!-- Hidden field to hold the final redirect URL -->
          <input type="hidden" id="redirect-url" name="redirect_url" value="">
          <!-- Hidden field to capture user quiz answers -->
          <input type="hidden" id="user-answers" name="user_answers" value="">
          <input type="text" name="first_name" placeholder="First Name" required style="display: block; margin-bottom:10px;">
          <input type="text" name="last_name" placeholder="Last Name" required style="display: block; margin-bottom:10px;">
          <input type="email" name="email" placeholder="Email" required style="display: block; margin-bottom:10px;">
          <h3>"Connect no commitment"</h3>
          <button type="submit" style="padding: 10px 20px;">Continue</button>
      </form>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Renders the first quiz question.
 * Asks the user to select their biggest current concern to personalize the quiz path.
 *
 * @return string HTML for the initial question block.
 */

function question_one() {
    ob_start();
    ?>
    <div id="question1" class="question-container">
        <h2 class="hide-main-question" style="text-align: center; color: #28a745;">What is your biggest worry at the present moment?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_childquestions" >My Children</button>
            <button class="next-question" data-target="show_workquestions" >Work</button>
            <button class="next-question" data-target="show_relationshipquestions" >My Relationship</button>
        </div>
        
    </div>
 
    <?php
    return ob_get_clean();
}

/**
 * Renders the relationship support branch options.
 * Displayed when user selects "My Relationship" in question one.
 *
 * @return string HTML for follow-up relationship category options.
 */

function show_relationshipquestions(){
    ob_start();
    ?>
    <div id="show_relationshipquestions" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What aspect of your relationship with your child do you want support with?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_communication" >Communication</button>
            <button class="next-question" data-target="show_discipline" >Discipline</button>
            <button class="next-question" data-target="show_coparenting" >Co-Parenting</button>
            <button class="next-question" data-target="show_singleparent" >Single Parenting</button>
            <button class="next-question" data-target="show_grandparentsupport" >Grandparent/Guardian Support</button>
            <button class="next-question" data-target="show_connectingwithchild" >Connecting with Your Child</button>
            <button class="next-question" data-target="show_newcomerparents" >Newcomer Parenting Support</button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
   
    <?php
    return ob_get_clean();
}

/**
 * Asks about child-parent communication.
 *
 * @return string HTML for the communication question.
 */
function show_communication(){
    ob_start();
    ?>
    <div id="show_communication" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Do you feel your child listens to you and expresses their thoughts openly? </h2>
        <div class="button-group">
            <button class="next-question" data-target="show_yescommunication">Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}

/**
 * Follow-up question when communication is a concern.
 *
 * @return string HTML for deeper communication issue options.
 */
function show_yescommunication(){
    ob_start();
    ?>
    <div id="show_yescommunication" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What specific communication challenges are you facing?  </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >My child does not talk to me about their feelings.</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >We argue frequently.</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >They don’t follow my instructions.</button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
   
    <?php
    return ob_get_clean();
}

/**
 * Displays the discipline question and branching.
 *
 * @return string HTML for discipline question.
 */
function show_discipline(){
    ob_start();
    ?>
    <div id="show_discipline" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Are you struggling with setting rules and boundaries that your child follows?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_yesdiscipline" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >No</button>
            <button id="back-button">Back</button>
        </div>
      
      
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Follow-up question to explore discipline challenges.
 *
 * @return string HTML for discipline options.
 */
function show_yesdiscipline(){
    ob_start();
    ?>
    <div id="show_yesdiscipline" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What behaviors are you most concerned about? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Defiance</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Aggression (Verbal/Physical)</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >School Discipline Issues</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Self-Regulation at Home</button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Displays co-parenting support options.
 *
 * @return string HTML for co-parenting questions.
 */
function show_coparenting(){
    ob_start();
    ?>
    <div id="show_coparenting" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What aspect of co-parenting do you need help with?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/co-parenting-single-parenting" >Conflict over parenting styles</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/co-parenting-single-parenting" >Improving your relationship with the other parent </button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/co-parenting-single-parenting" >Emotional well-being of my child</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Displays questions specific to single parents.
 * This section is shown when the user selects "Single Parenting" from the relationship options.
 *
 * @return string HTML with support options for single parenting challenges.
 */
function show_singleparent(){
    ob_start();
    ?>
    <div id="show_singleparent" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What type of support do you need as a single parent?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/co-parenting-single-parenting" >Navigating parenting challenges alone</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/co-parenting-single-parenting" >Building confidence in my parenting journey</button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Displays a question to identify if the user is a grandparent/guardian caregiver.
 *
 * @return string HTML for grandparent support initial question.
 */
function show_grandparentsupport(){
    ob_start();
    ?>
    <div id="show_grandparentsupport" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Are you the primary caregiver for your grandchild? </h2>
        <div class="button-group">
            <button class="next-question" data-target="show_yessupport" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/a-guiding-light-for-modern-grandparenting" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Displays challenges faced by grandparent caregivers.
 * This appears when user selects "Yes" to being the primary caregiver.
 *
 * @return string HTML with grandparent-specific parenting challenges.
 */
function show_yessupport(){
    ob_start();
    ?>
    <div id="show_yessupport" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What challenges do you face in this role? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/a-guiding-light-for-modern-grandparenting" >Managing parenting responsibilities at this stage of life</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/a-guiding-light-for-modern-grandparenting" >Navigating generational differences in parenting styles</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/a-guiding-light-for-modern-grandparenting" >Supporting my grandchild’s emotional well-being</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Asks the user if they feel emotionally connected with their child.
 *
 * @return string HTML for the emotional connection question.
 */
function show_connectingwithchild(){
    ob_start();
    ?>
    <div id="show_connectingwithchild" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Do you feel emotionally connected with your child?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_yesconnecting" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Follow-up question for parents who feel connected but still face challenges.
 *
 * @return string HTML with detailed connection issues.
 */
function show_yesconnecting(){
    ob_start();
    ?>
    <div id="show_yesconnecting" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What are the main challenges in your relationship? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Lack of quality time together</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Struggles in understanding their emotions</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Difficulty in engaging in meaningful conversations</button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Displays a question to check if the user is a newcomer parent.
 *
 * @return string HTML for the newcomer parent check.
 */
function show_newcomerparents(){
    ob_start();
    ?>
    <div id="show_newcomerparents" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Have you faced challenges adjusting to parenting in a new country? </h2>
        <div class="button-group">
            <button class="next-question" data-target="show_yesnewparenting" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/newcomer-coaching" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}
/**
 * Shows specific challenge options for newcomer parents.
 * Appears when user confirms they’ve had difficulty adapting.
 *
 * @return string HTML for newcomer parent difficulties.
 */
function show_yesnewparenting(){
    ob_start();
    ?>
    <div id="show_yesnewparenting" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What challenges are you facing as a newcomer parent? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/newcomer-coaching" >My child’s adaptation to school</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/newcomer-coaching" >Language or cultural barriers</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/newcomer-coaching" >Building a support network</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}

/**
 * First question in the Work-related path.
 * Asks whether the user feels overwhelmed balancing parenting and other responsibilities.
 *
 * @return string HTML for the initial work concern question.
 */

function show_workquestions(){
    ob_start();
    ?>
    <div id="show_workquestions" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Do you often feel overwhelmed trying to balance parenting with your other responsibilities?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_yeswork" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
}

/**
 * Follow-up when user confirms difficulty balancing work and family.
 *
 * @return string HTML asking about specific work/home conflict.
 */

function show_yeswork(){
    ob_start();
    ?>
    <div id="show_yeswork" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >Do you struggle with balancing your work and home responsibilities?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_sourceofstress" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
   
    <?php
    return ob_get_clean();
    
}
/**
 * Asks user to identify the biggest source of stress in their work-life balance.
 *
 * @return string HTML with branching or final-answer buttons.
 */
function show_sourceofstress(){
    ob_start();
    ?>
    <div id="show_sourceofstress" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >What is the biggest source of stress in your work-life balance?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_wellbeing" >Maintaining personal well-being while balancing work & family life</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach" >Advancing my career while staying present for my child</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/dietetics" >Providing nutritious meals for my family despite a busy schedule</button>
            <button class="next-question" data-target="show_copingstress" >Coping with stress and parenting demands through mindfulness </button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Follow-up branching between personal well-being or managing work/family.
 *
 * @return string HTML with two options leading to further questions.
 */
function show_wellbeing(){
    ob_start();
    ?>
    <div id="show_wellbeing" style="display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question" >what you think is more difficult to manage?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_personalwellbeing" >personal well-being</button>
            <button class="next-question" data-target="show_workresponsibilities" >work and family responsibilities</button>
            <button id="back-button">Back</button>
            
        </div>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Explores the user's specific struggles related to personal well-being.
 *
 * @return string HTML with final answers directing to coaches.
 */
function show_personalwellbeing(){
    ob_start();
    ?>
    <div id="show_personalwellbeing" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What aspect of personal well-being do you struggle with the most?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/dietetics"  >Physical health </button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents" >Emotional well-being </button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach"  >Time for self-care </button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach"  >Mental overload </button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
    <?php
    return ob_get_clean();

}
/**
 * Explores challenges related to managing work and family responsibilities.
 *
 * @return string HTML with coaching redirection options.
 */

function show_workresponsibilities(){
    ob_start();
    ?>
    <div id="show_workresponsibilities" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What aspect of work and family balance is most challenging for you?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Keeping up with household and parenting duties while managing work</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Feeling guilty about not spending enough quality time with my children </button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents"  >Struggling with work-related stress that impacts my home life</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/help-me-find-a-coach"  >Managing time effectively between work, family, and personal needs</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Introduces questions on how stress affects daily parenting.
 *
 * @return string HTML with emotional response options branching to detailed answers.
 */
function show_copingstress(){
    ob_start();
    ?>
    <div id="show_copingstress" style="display: none; margin-top: 30px;" class="question-container" >
    <h2 class="hide-main-question" >How does stress impact your daily parenting experience?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_option1" >I often feel overwhelmed and emotionally drained</button>
            <button class="next-question" data-target="show_option2">I struggle with staying patient and calm with my child</button>
            <button class="next-question" data-target="show_option3" >I find it hard to be present and enjoy time with my family</button>
            <button class="next-question" data-target="show_option4" >I have trouble sleeping or relaxing due to constant stress</button>
            <button id="back-button">Back</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Explores availability of self-care time based on stress.
 *
 * @return string HTML for coaching redirection based on time for self-care.
 */
function show_option1(){
    ob_start();
    ?>
    <div id="show_option1" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >Do you feel like you have time for self-care?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents"  >Yes, but I don’t know where to start</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents" >No, I can’t find time for myself</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Identifies key stress triggers affecting parenting patience.
 *
 * @return string HTML with two trigger-based coaching options.
 */
function show_option2(){
    ob_start();
    ?>
    <div id="show_option2" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What triggers your stress the most?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents"  >My child’s behavior and emotional reactions</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents" >The demands of work and home responsibilities</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Explores barriers to emotional presence with the family.
 *
 * Triggered after user selects feeling disconnected during stressful periods.
 *
 * @return string HTML with stress-related options that block engagement.
 */
function show_option3(){
    ob_start();
    ?>
    <div id="show_option3" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What do you think prevents you from being fully engaged?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents"  >Constant worries and distractions</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents" >Feeling exhausted and burned out</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Asks if user has attempted relaxation techniques.
 * Offers guidance whether they've tried or not.
 *
 * @return string HTML for mindfulness/relaxation coaching options.
 */
function show_option4(){
    ob_start();
    ?>
    <div id="show_option4" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >Have you tried any relaxation techniques?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents"  >Yes, but they don’t seem to work</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents" >No, I don’t know what to try</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Shown when user selects “My Children” from the initial quiz.
 * Filters whether they’re actively looking for help.
 *
 * @return string HTML for basic confirmation question.
 */

function childquestions(){
    ob_start();
    ?>
    <div id="show_childquestions" style=" display: none; margin-top: 30px;" class="question-container">
        <h2 class="hide-main-question">Are you researching ways to help your children thrive?</h2>
        <div class="button-group">
            <button class="next-question" data-target="Yes" >Yes</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/1" >No</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Presents main child-related support areas after user confirms interest.
 *
 * @return string HTML with child development concerns to personalize quiz path.
 */
function Yes(){
    ob_start();
    ?>
    <div id="Yes" style="display: none; margin-top: 30px;" class="question-container" >
    <h2 class="hide-main-question" >What are your biggest concerns with your child/children?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_learningdevelopment" >Learning & Development</button>
            <button class="next-question" data-target="show_friendandsocialskills" >Friend & Social Skills</button>
            <button class="next-question" data-target="show_behavioralconcerns" >Behavioral Concerns</button>
            <button class="next-question" data-target="show_balancingextraandacademics" >Balancing Extracurricular & Academics</button>
            <button class="next-question" data-target="show_emotionalwellbeing" >Emotional Well-Being</button>
            <button class="next-question" data-target="show_healthconcerns" >Health Concerns</button>
            <button id="back-button">Back</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Breaks down specific learning challenges for deeper follow-up.
 *
 * @return string HTML for detailed academic support topics.
 */
function show_learningDevelopment(){
    ob_start();
    ?>
    <div id="show_learningdevelopment" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What are your biggest concerns with your child's learning?</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_learningdifficulties" >Learning Difficulties</button>
            <button class="next-question" data-target="show_motivation" >Motivation</button>
            <button class="next-question" data-target="show_curriculum" >Understanding the Curriculum</button>
            <button class="next-question" data-target="show_academic" >Overall Academic Achievement</button>
            <button class="next-question" data-target="show_reading" >Reading</button>
            <button class="next-question" data-target="show_giftedness" >Giftedness</button>
            <button id="back-button">Back</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Helps identify diagnosed or suspected learning difficulties.
 *
 * Each selection links to a specialized staff member.
 *
 * @return string HTML for specific learning diagnoses.
 */
function show_learningDifficulties(){
    ob_start();
    ?>
    <div id="show_learningdifficulties" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >Does your child have one of the following?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/1"  >ADHD</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/21" >Learning Disability</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/6"  >Language Impairment</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/26"  >Autism</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/10"     >Hearing Impairment</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/44"  >Vision Impairment</button>
            <button id="back-button">Back</button>
        </div>
       
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Identifies motivation-related academic barriers.
 * Leads to further exploration based on interest areas.
 *
 * @return string HTML for motivation path.
 */
function show_motivation(){
    ob_start();
    ?>
    <div id="show_motivation" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What issues are affecting your child's motivation</h2>
        <div class="button-group">
            <button class="next-question" data-target="show_age" >Academic Pressure</button>
            <button class="next-question" data-target="show_extracurriculars" >Extracurriculars (Sports, Arts, Music)</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/3" >Social Interactions</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Identifies motivation-related academic barriers.
 * Leads to further exploration based on interest areas.
 *
 * @return string HTML for motivation path.
 */
function show_age(){
    ob_start();
    ?>
    <div id="show_age" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >How old is your child?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/29">5 and under</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/44" >6-9 Years</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/37" >10-18 Years</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Displays options for the type of extracurricular activities the child is involved in.
 * This helps connect families with coaches experienced in those areas.
 *
 * @return string HTML with extracurricular activity options.
 */
function show_extracurriculars(){
    ob_start();
    ?>
    <div id="show_extracurriculars" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >In which activities?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/37" >Sports</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/47" > Arts And Music</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Displays age options to identify curriculum understanding challenges.
 * 
 * Used when the user selects “Understanding the Curriculum” as a concern.
 * Routes to different coaches based on the child’s age group.
 *
 * @return string HTML structure with buttons for age-based curriculum support.
 */
function show_curriculum(){
    ob_start();
    ?>
    <div id="show_curriculum" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >How old is your child? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/29" >5 and under</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/40" > 6 to 9 years</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/13" >10 to 13 yearsr</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/8" > 14 to 18 years</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Displays age options to assess overall academic performance concerns.
 * 
 * Triggered when a parent is unsure about their child's academic progress.
 * Routes to academic coaches based on the child’s age.
 *
 * @return string HTML with final options linking to age-appropriate academic help.
 */
function show_academic(){
    ob_start();
    ?>
    <div id="show_academic" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question">How old is your child? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/kindergarten-parent-coach" >5 and under</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/academic-support-for-the-primary-child-grade-1-4" > 6 to 9 years</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parenting-teens-through-their-academic-journey" >10 to 18 yearsr</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Displays reading difficulty support options based on the child’s age.
 * 
 * All age groups redirect to the same reading intervention team.
 *
 * @return string HTML with reading support buttons for multiple age ranges.
 */
function show_reading(){
    ob_start();
    ?>
    <div id="show_reading" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >How old is your child? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/reading-intervention-support" >5 and under</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/reading-intervention-support" > 6 to 9 years</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/reading-intervention-support" >10 to 13 yearsr</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/reading-intervention-support" > 14 to 18 years</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Shows specific challenges related to raising a gifted child.
 * 
 * Helps parents identify concerns like asynchronous development or emotional issues.
 * Redirects to a coach experienced in gifted education.
 *
 * @return string HTML for selecting a giftedness-related concern.
 */
function show_giftedness(){
    ob_start();
    ?>
    <div id="show_giftedness" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What is your primary concern related to your gifted child?</h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/gifted-child" >Asynchronous Development </button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/gifted-child" >Emotional Well-Being</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/gifted-child" >Academic Guidance</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Displays social concerns parents might have about their children.
 * 
 * Includes issues like making friends, lack of friendships, or social media exposure.
 * All responses lead to a follow-up question on the child’s age.
 *
 * @return string HTML with common social development concerns.
 */
function show_friendandsocialskills(){
    ob_start();
    ?>
    <div id="show_friendandsocialskills" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question">What social concerns do you have about your child?</h2>
        <div class="button-group">
            <button class="next-question"  data-target="show_socialage" >Difficulties Making Friends </button>
            <button class="next-question"  data-target="show_socialage" >Lack Of Friendships</button>
            <button class="next-question"  data-target="show_socialage" >Social Media Exposure</button>
            <button class="next-question"  data-target="show_socialage" >School Discipline Issues</button>
            <button id="back-button">Back</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Displays age-specific routing for social concern support.
 * 
 * Called after parent selects a social issue.
 * Routes to targeted emotional/social development coaching based on age.
 *
 * @return string HTML with buttons for 3 age groups.
 */
function show_socialage(){
    ob_start();
    ?>
    <div id="show_socialage" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >How old is your child? </h2>
        <div class="button-group">
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/social-emotional-learning-behaviour-support-for-parents-with-children-4-9-years-of-age" > 4 to 9 years</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-parents-with-social-emotional-struggles-for-pre-teen-and-teens" >10 to 13 yearsr</button>
            <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-parents-with-social-emotional-struggles-for-pre-teen-and-teens" > 14 to 18 years</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}


/**
 * Displays behavioral concerns like discipline or aggression.
 * 
 * Two of the responses route to a follow-up question asking for age.
 * Others go directly to behavioral support coaches.
 *
 * @return string HTML for choosing behavior-related concerns.
 */

function show_behavioralconcerns(){
    ob_start();
    ?>
    <div id="show_behavioralconcerns" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question">What behavior is most concerning for you about your child?</h2>
        <div class="button-group">
            <button class="next-question"  data-target="show_behavioralage" >School Discipline Issues </button>
            <button class="next-question"  data-target="show_behavioralage" >Self-Regulation (Home/School)</button>
            <button class="final-answer"  data-url="https://parentpulsecoaching.janeapp.com/#/supporting-parents-with-social-emotional-struggles-for-pre-teen-and-teens" >Defiance</button>
            <button class="final-answer"  data-url="https://parentpulsecoaching.janeapp.com/#/supporting-parents-with-social-emotional-struggles-for-pre-teen-and-teens" >Physical / Verbal Aggression</button>
            <button id="back-button">Back</button>
        </div>
        
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Shows age options to help match behavioral challenges to appropriate coach.
 * 
 * Follows up after user selects School Discipline or Self-Regulation concerns.
 *
 * @return string HTML with age-based routing for behavior help.
 */
function show_behavioralage(){
    ob_start();
    ?>
    <div id="show_behavioralage" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question">How old is your child? </h2>
        <div class="button-group">
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/social-emotional-learning-behaviour-support-for-parents-with-children-4-9-years-of-age"> 4 to 9 years</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-parents-with-social-emotional-struggles-for-pre-teen-and-teens" >10 to 16 years</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/staff_member/1" > 16 to 18 years</button>
        <button id="back-button">Back</button>    
    </div>
    
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Asks which type of extracurricular activity the child is involved in.
 * 
 * Helps guide parents looking for help balancing academics with other activities.
 * Routes to specialized coaches based on the child’s focus area.
 *
 * @return string HTML for sports, dance, drama, or music support.
 */
function show_balancingextraandacademics(){
    ob_start();
    ?>
    <div id="show_balancingextraandacademics" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What type of extracurricular activity is your child involved in?</h2>
        <div class="button-group">
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/high-performance-athletes" > Sports</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-your-child-through-the-arts-dance-music-drama-coaching" >Dance</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-your-child-through-the-arts-dance-music-drama-coaching" >Drama</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-your-child-through-the-arts-dance-music-drama-coaching" >Music</button>
        <button id="back-button">Back</button>    
         </div>
    
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Lets parents select emotional wellbeing concerns for their child.
 * 
 * Includes issues like anxiety, grief, mindfulness, or self-regulation.
 * If anxiety is selected, age is asked next. Others go straight to relevant coaches.
 *
 * @return string HTML for choosing emotional challenges.
 */
function show_emotionalwellbeing(){
    ob_start();
    ?>
    <div id="show_emotionalwellbeing" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question">What emotional challenges are you concerned about?</h2>
        <div class="button-group">
        <button class="next-question" data-target="show_ageforemotional" > Anxiety</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/parent-pulse-coach" >Grief</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/mindfulness-coaching-for-parents">Mindfulness Strategies</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/social-emotional-learning-behaviour-support-for-parents-with-children-4-9-years-of-age" >Self-Regulation</button>
        <button id="back-button">Back</button>    
         </div>
    
    </div>
    <?php
    return ob_get_clean(); 
}
/**
 * Follows up on emotional issues (like anxiety) with an age question.
 * 
 * Age helps connect parents to appropriate emotional wellbeing coaches.
 *
 * @return string HTML with 2 age group options for emotional coaching.
 */
function show_ageforemotional(){

    ob_start();
    ?>
    <div id="show_ageforemotional" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question">How old is your child? </h2>
        <div class="button-group">
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/social-emotional-learning-behaviour-support-for-parents-with-children-4-9-years-of-age">4 to 9 Years</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/supporting-parents-with-social-emotional-struggles-for-pre-teen-and-teens">10 to 18 Years</button>
        <button id="back-button">Back</button>    
    </div>
    
    </div>
    <?php
    return ob_get_clean(); 
}
/**
 * Asks what kind of health concern the parent is dealing with.
 * 
 * If “Health Concerns” is selected, another set of condition-specific questions are shown.
 * “Nutrition Concerns” redirects immediately.
 *
 * @return string HTML with health category choices.
 */
function show_healthconcerns(){
    ob_start();
    ?>
    <div id="show_healthconcerns" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >What area of your child's health are you most concerned about?</h2>
        <div class="button-group">
        <button class="next-question" data-target="show_healthoptions" >Health Concerns</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/dietetics" >Nutrition Concerns</button>
        <button id="back-button">Back</button>    
        </div>
        
    </div>
    <?php
    return ob_get_clean(); 
}
/**
 * Lets parents select a specific health condition.
 * 
 * This is triggered after choosing “Health Concerns” in the previous step.
 * Each condition leads to a coach specialized in that domain.
 *
 * @return string HTML with epilepsy, diabetes, vision, and hearing support options.
 */
function show_healthoptions(){
    ob_start();
    ?>
    <div id="show_healthoptions" style="display: none; margin-top: 30px;" class="question-container">
        <h2  class="hide-main-question" >Please specify the health condition you are concerned about?</h2>
        <div class="button-group">
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/epilepsy-parent-coaching" >Epilepsy</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/dietetics" >Diabetes</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/low-vision-and-blind-parent-coaching" >Vision</button>
        <button class="final-answer" data-url="https://parentpulsecoaching.janeapp.com/#/deaf-and-hard-of-hearing-support" >Hearing</button>
        <button id="back-button">Back</button>
        </div>
       
    </div>
    <?php
    return ob_get_clean(); 

}



