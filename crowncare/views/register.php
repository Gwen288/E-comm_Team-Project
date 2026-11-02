<?php    

require_once  __DIR__ . "/../core/core.php";
require_once __DIR__ . "/layout/header.php";

?>


<main>
<h1> Create an Account</h1>

<?php if (isset($_SESSION["error"])): ?>

    <div class="error-message">
        <?php echo htmlspecialchars($_SESSION["error"]); ?>
</div>

<?php unset($_SESSION["error"]); ?>

<?php endif; ?>

<form id="registerationFrom"  action="../actions/register_action.php" method="POST">
<!-- Full Name --> <div> 
    <label for="name">Full Name</label> 
    <input type="text" id="name" name="name" required > 
    <span class="field-error" id="nameError">

    </span> 

</div>
<!-- Email --> 
 <div> 
    <label for="email">Email</label> 
    <input type="email" id="email" name="email" required >
     <span class="field-error" id="emailError"></span>

     </div> <!-- Password --> 
     
     <div> 
        <label for="pass">Password</label>
         <input type="password" id="pass" name="pass" required >
          <span class="field-error" id="passError"></span> 
        </div> 

        <!-- Country -->
          <div> 
            <label for="country">Country</label> 

            <select id="country" name="country" required > 
            <option value="">Select Country</option>
             <option value="Ghana">Ghana</option> 
             <option value="Nigeria">Nigeria</option>
              <option value="Kenya">Kenya</option> 
              <option value="United Kingdom">United Kingdom</option>
               <option value="United States">United States</option>
             </select>

              <span class="field-error" id="countryError"></span> 
            </div> 

            <!-- City --> 
             <div> 
                <label for="city">City</label> 
                <input type="text" id="city" name="city" required >
                 <span class="field-error" id="cityError"></span> 
                </div> 
                
            <!-- Contact Number --> 
             <div> 
                <label for="contact">Contact Number</label>
                 <input type="tel" id="contact" name="contact" required > 
                 <span class="field-error" id="contactError"></span> 
                </div> 

            <!-- Submit --> 
             <button type="submit" id="registerButton" > Create Account </button>

             </form> 
            </main>            
    <?php require_once __DIR__ . '/layout/footer.php'; ?>

