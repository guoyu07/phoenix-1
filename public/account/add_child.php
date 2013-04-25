<?php

/**
 * Family dashboard
 *
 * @author  Yectep Studios <info@yectep.hk>
 * @version 30104
 * @package Plume
 */


define('PTP',   '../../private/');
define('PHX_SCRIPT_TYPE',   'HTML');
define('PHX_UX',        true);
define('PHX_STUDENT',    true);


// Include common ignition class
require_once(PTP . 'php/ignition.php');

// We require a staff login for this page
if (!ACL::checkLogin('public')) {
    header('Location: ./login.php?msg=error_nologin');
    exit();
} else {
    $_fam = new FamStu('family', $_SESSION['SSOID']);
    $_fam->getChildren();
    $_fam->cos = $_fam->getCosStatus();
}

// Insert child if required by submission key
if (isset($_GET['act']) && $_GET['act'] = "submit") {

    if (isset($_POST['lname']) && isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['dob']) && isset($_POST['gender']) && isset($_POST['ename']) && isset($_POST['ephone']) && isset($_POST['erel']) && isset($_POST['school_name'])) {
        $cos = (($_fam->cos && ($_POST['cos'] == '1')) ? 1: 0);
        $pname = ((strlen($_POST['pname']) == 0) ? $_POST['fname'] : $_POST['pname']);
        $pkey = sha1(rand(1,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));

        try {
            $stmt = Data::prepare('INSERT INTO `students` (`FamilyID`, `StudentPrivateKey`, `StudentNameLast`, `StudentNameGiven`, `StudentNamePreferred`, `StudentDOB`, `StudentSchool`, `StudentGender`, `StudentCTS`, `StudentSubmitted`, `StudentECName`, `StudentECPhone`, `StudentECRelationship`, `StudentMedCondition`, `StudentMedMedications`, `StudentCOS`) VALUES (:famid, :key, :lname, :fname, :pname, :dob, :school, :gender, NOW(), 0, :ecname, :ecphone, :ecrel, :medcond, :medmeds, :cos)');
            $stmt->bindParam('famid', $_fam->fid, PDO::PARAM_INT);
            $stmt->bindParam('key', $pkey, PDO::PARAM_STR);
            $stmt->bindParam('lname', substr($_POST['lname'], 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('fname', substr($_POST['fname'], 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('pname', substr($pname, 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('dob', $_POST['dob']);
            $stmt->bindParam('school', substr($_POST['school_name'], 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('gender', substr($_POST['gender'], 0, 1), PDO::PARAM_STR);
            $stmt->bindParam('ecname', substr($_POST['ename'], 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('ecphone', substr($_POST['ephone'], 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('ecrel', substr($_POST['erel'], 0, 255), PDO::PARAM_STR);
            $stmt->bindParam('medcond', $_POST['med_cond'], PDO::PARAM_STR);
            $stmt->bindParam('medmeds', $_POST['med_mds'], PDO::PARAM_STR);
            $stmt->bindParam('cos', $cos, PDO::PARAM_INT);
            $stmt->execute();

            header('Location: /account/dashboard.php');
            exit();
        } catch (PDOException $e) {
            Common::throwNiceDataException($e);
        }
    } else {
        // Something is missing
        header('Location: /account/add_child.php?msg=err_missing');
        exit();
    }
}

// Triage and get default staff page
$h['title'] = 'Add a Child';
$n['my_name'] = $_fam->data['FamilyName'];

// Page variables
$p['cos'] = (($_fam->cos) ? '<div class="col-row" id="cos-info" style="margin-top:1em;">
        
        <!-- Form block -->
        <div id="form-prefs" class="col c7">
            <h2>Child-of-Staff Declaration</h2>
            
            <p>From what you have entered in your family account details, you are eligible to declare this student with <strong>Child-of-Staff</strong> status. Immediate children of CIS staff are eligible to attend the Summer Program free of charge. To declare this child as <strong>YOUR immediate descendant</strong>, please tick the box below.</p>

            <div class="form-hint"><label style="width:100%;font-size:1.3em;"><input type="checkbox" checked="unchecked" name="cos" value="1" /> I, a member of CIS staff, declare that this student is my immediate child.</label></div>
            
        </div>
        
        <!-- Help block -->
        <div id="help-prefs" class="col c3 hide-smartphone" style="background-color: #fafafa;">
            <h3>Preferred Spoken Language</h3>
            <p>While not always possible due to personnel reasons, we will try to call you in this language for your convenience. Please note that only English will be used in written communication.</p>
        </div>
    </div>' : '');

switch ($_GET['msg']) {
    case 'new_acct':
        $p['error'] = '<div class="alert alert-yellow">Our system shows that you haven\'t registered a child yet. Please add your first child before going to make a schedule!</div>';
    break;
    case 'err_missing':
        $p['error'] = '<div class="alert alert-red"><span class="badge badge-red">Missing field</span> Oops, it seems that you forgot one or more of the required fields!</div>';
    break;
    case 'err_bday_format':
        $p['error'] = '<div class="alert alert-red"><span class="badge badge-red">Formatting</span> Oops, it seems that we cannot validate your child\'s Date of Birth. Please make sure you enter in YYYY-MM-DD format, with leading zeroes (1997-03-21 for March 21, 1997).</div>';
    break;
    default:
        $p['error']  ='';
    break;
}

// Include header section
echo UX::makeHead($h, $n, 'common/header_public', 'common/nav_account');

// Page info
echo UX::makeBreadcrumb(array(  'My Family' => "/account/dashboard.php", 'Add a Child' => "/account/add_child.php"));
echo UX::grabPage('account/add_child', $p, true);

// Before footer grab time spent
$t['end'] = microtime(true);
$time = round(($t['end'] - $t['start']), 3);

echo UX::grabPage('common/masthead', array('time' => $time), true);
echo UX::grabPage('common/footer', null, true);

?>

