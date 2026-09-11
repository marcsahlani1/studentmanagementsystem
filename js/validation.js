jQuery(document).ready(function(){
                jQuery("#dob").datepicker({
                    dateFormat: "yy-mm-dd", //yy represents 4 digits year in jquery, y:2 digits
                    changeMonth: true,
                    changeYear: true,
                    yearRange: "-100:+0"
                });
            });

jQuery("#form").on("submit",function(event){
    let errormsg = "";
    jQuery("#js-errormsg").text(""); //CLEAR PREVIOUS ERROR MESSAGES
    // VALIDATE DATE OF BIRTH
    const dobVal = jQuery("#dob").val().trim();
    const dobRegex = /^\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01])$/;
    if(dobVal === ""){
        errormsg = "Date of Birth is required!";
    }else if(!dobRegex.test(dobVal)){
        errormsg = "Invalid date format! Please use YYYY-MM-DD.";
    }
    //VALIDATE PROFILE PICTURE
    const fileInput = jQuery("#pfp")[0];
    if(fileInput.files.length > 0){
        const file = fileInput.files[0];
        const fileSize = file.size;
        const fileName = file.name;
        const fileExt = fileName.split('.').pop().toLowerCase();
        const allowedExt = ['jpg','jpeg','png','gif'];
        const maxSize = 2 * 1024 * 1024 ;
        if(!allowedExt.includes(fileExt)){
            errormsg = "Invalid file type! Only jpg, jpeg, png and gif.";
        }else if(fileSize > maxSize){
            errormsg = "Maximum allowed size is 2MB.";
        }
    }
    const usernameVal = jQuery("#username").val().trim();
    if(usernameVal === ""){
        errormsg = "Username is required!";
    }
    const emailVal = jQuery("#email").val().trim();
    if(emailVal === ""){
        errormsg = "Email is required!";
    }
    const passVal = jQuery("#pass").val().trim();
    if(passVal === ""){
        errormsg = "Password is required!";
    }
    const fnameVal = jQuery("#fname").val().trim();
    if(fnameVal === ""){
        errormsg = "First Name is required!";
    }
    const lnameVal = jQuery("#lname").val().trim();
    if(lnameVal === ""){
        errormsg = "Last Name is required!";
    }
    const phoneVal = jQuery("#phone").val().trim();
    if(phoneVal === ""){
        errormsg = "Phone number is required!";
    }
    const genderVal = jQuery("#gender").val().trim();
    if(genderVal !=="male" && genderVal !== "female"){
        errormsg = "Please enter a valid gender!";
    }
    const addressVal = jQuery("#user_address").val().trim();
    if(addressVal === ""){
        errormsg = "Address is required!";
    }
    if(errormsg !== ""){//IF THERE'S AN ERROR: DON'T SUBMIT!
        event.preventDefault();//PREVENT FORM FROM SUBMITTING TO PHP
        jQuery("#js-errormsg").text(errormsg);// DISPLAY THE ERROR MESSAGE
    }
});