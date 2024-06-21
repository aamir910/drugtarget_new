<?php

include("fetchdata.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if all array values are set
  if (
    isset($_POST['Chembl_id1']) ||
    isset($_POST['MaxPhase1']) ||
    isset($_POST['oncotree_change1']) ||
    isset($_POST['DataPlatform']) ||
    isset($_POST['pic50']) ||
    isset($_POST['disease_class1']) .
    isset($_POST['compound_class1'])


  ) {
    // Array to store conditions
    $conditions = array();


    $sql = "SELECT drugresponse.*, compounds_updated1.INCHI_KEY ,	compounds_updated1.COMPOUND_CLASS, drug_disease.Disease_class, 
    drug_disease.Disease_name , drug_disease.Phase FROM drugresponse";

    // Join with compounds_updated1 table
    $sql .= " LEFT JOIN compounds_updated1 ON drugresponse.COMPOUND_NAME = compounds_updated1.COMPOUND_NAME";

    // Join with drug_disease table
    $sql .= " LEFT JOIN drug_disease ON compounds_updated1.INCHI_KEY = drug_disease.INCHI_KEY";

    $sql .= " WHERE";


    // Check and add condition for ONCOTREE_PRIMARY_DISEASE
    if (isset($_POST['Chembl_id1']) && !empty($_POST['Chembl_id1'])) {
      $Chembl_id1 = $_POST['Chembl_id1'];
      $escaped_chembl_ids = array_map(function ($value) use ($conn) {
        return mysqli_real_escape_string($conn, $value);
      }, $Chembl_id1);

      $Chembl_id_condition = implode("','", $escaped_chembl_ids);
      $conditions[] = "drugresponse.ONCOTREE_PRIMARY_DISEASE IN ('$Chembl_id_condition')";
    }

    // Check and add condition for MAX_PHASE
    if (isset($_POST['MaxPhase1']) && !empty($_POST['MaxPhase1'])) {
      $MaxPhase1 = $_POST['MaxPhase1'];
      $MaxPhase_condition = implode("','", $MaxPhase1);
      $conditions[] = "drugresponse.MAX_PHASE IN ('$MaxPhase_condition')";
    }

    // Check and add condition for pic50
    if (isset($_POST['pic50']) && !empty($_POST['pic50'])) {
      $pic50 = floatval($_POST['pic50']); // Convert pic50 to a float value
      $conditions[] = "VALUE >= $pic50";
    }

    // Check and add condition for ONCOTREE_LINEAGE
    if (isset($_POST['oncotree_change1']) && !empty($_POST['oncotree_change1'])) {
      $oncotree_change1 = $_POST['oncotree_change1'];
      $oncotree_change_condition = implode("','", $oncotree_change1);
      $conditions[] = "drugresponse.ONCOTREE_LINEAGE IN ('$oncotree_change_condition')";
    }

    if (isset($_POST['DataPlatform']) && !empty($_POST['DataPlatform'])) {
      $DataPlatform = $_POST['DataPlatform'];
      $DataPlatform_condition = implode("','", $DataPlatform);
      $conditions[] = "drugresponse.DATASET IN ('$DataPlatform_condition')";
    }
    // for the data of the drug_disease  

    if (isset($_POST['disease_class1']) && !empty($_POST['disease_class1'])) {
      $disease_class1 = $_POST['disease_class1'];
      $disease_class1_condition = implode("','", $disease_class1);
      $conditions[] = "drug_disease.Disease_class IN ('$disease_class1_condition')";
    }
    // for the data of the compound class   

    if (isset($_POST['compound_class1']) && !empty($_POST['compound_class1'])) {
      $compound_class1 = $_POST['compound_class1'];
      $compound_class1_condition = implode("','", $compound_class1);
      $conditions[] = "compounds_updated1.COMPOUND_CLASS IN ('$compound_class1_condition')";
    }

    $count_increment = intval($_POST['count_increment']);

    if (!empty($conditions)) {
      // $sql = "SELECT * FROM drugresponse WHERE " . implode(" AND ", $conditions) ;

      if ($count_increment != 1) {

        $sql .= " " . implode(" AND ", $conditions);
      } else {
        $sql .= " " . implode(" AND ", $conditions) . " AND drugresponse.MAX_PHASE NOT IN ('Preclinical', 'Unknown')";
      }
    }

    // Limit the result to 400 rows
    $limit = 200 * $count_increment;

    // Append the LIMIT clause to your SQL query
    $sql .= " LIMIT " . $limit;

    $result = $conn->query($sql);

    // Fetch the result into an associative array
    $data = array();
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }

    // Convert the result to JSON format
    $json_result = json_encode($data);
    header('Content-Type: application/json');

    // Echo the JSON-encoded data
    echo $json_result;
    exit(); // Stop further execution
  }
}
?>


<?php

// Check if drugName is set in the POST request
if (isset($_POST['drugName'])) {

  include 'fetchdata.php';
  $drugName = $_POST['drugName'];

  // Use prepared statement to prevent SQL injection
  $stmt = $conn->prepare("SELECT * FROM compounds_updated1 WHERE COMPOUND_NAME = ?");
  $stmt->bind_param("s", $drugName);
  $stmt->execute();

  // Get the result
  $result = $stmt->get_result();

  // Fetch data as an associative array
  $data = $result->fetch_all(MYSQLI_ASSOC);

  // Close the statement
  $stmt->close();

  // Return the data as JSON
  echo json_encode($data);
  $conn->close();
  exit();
}

// Close the database connection
?>
<?php
// Check if drugName is set in the POST request
if (isset($_POST['drugName2'])) {

  include 'fetchdata.php';
  $drugName2 = $_POST['drugName2'];

  // Use prepared statement to prevent SQL injection
  $stmt = $conn->prepare("SELECT * FROM cellline WHERE CELL_LINE_NAME = ?");
  $stmt->bind_param("s", $drugName2);
  $stmt->execute();

  // Get the result
  $result = $stmt->get_result();

  // Fetch data as an associative array
  $data = $result->fetch_all(MYSQLI_ASSOC);

  // Close the statement
  $stmt->close();

  // Return the data as JSON
  echo json_encode($data);
  // echo $drugName2;
  $conn->close();
  exit();
}
// Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <!-- Include Bootstrap 5 CSS and JavaScript -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="./css/styles.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>


</head>
<style>
  #customOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(10px);
    /* Increased blur effect */
    z-index: 9999;
    /* Higher z-index */
  }

  #customInteractiveDiv {
    width: 90%;
    height: 90%;
    /*width: 500px;*/
    /* height: 500px; */
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    z-index: 9999;
    /* Higher z-index */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    /* Added box-shadow */
  }

  #customCloseButton {
    background-color: white;
    position: absolute;
    top: 5px;
    right: 5px;
    margin-top: -5px;
    /* Adjusted margin to make the close button closer */
    margin-right: -5px;
    /* Adjusted margin to make the close button closer */
  }

  #customSearchBar {
    display: block;
    margin-top: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: calc(100% - 30px);
    /* Adjusted width to accommodate for the close button 
   /* Increase the height as desired */
    resize: vertical;
    /* Increased margin to create space for the close button */
    width: 100%;
    height: 80%;
  }

  #customSubmitButton {
    display: block;
  }

  .search-bar3 {
    margin-top: 10px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
  }

  #customSearchBar:focus,
  #customSearchBar:active {
    border-color: #28a5fb;
    /* Change the border color on focus */
    box-shadow: 0 0 5px #28a5fb;
    /* Add a shadow effect on focus */
  }
</style>

<body>
  <div class=" searchBar">
    <form class="selection_box flex" id="searchForm">
      <div class="in_de_Crement">
        <!-- heading  -->
        <p style="/* text-align:center; */display: flex;justify-content: center;align-items: center;margin-bottom: 0px;width: 5rem;;">More data </p>
        <button class="btn1" id="increment" title='fetch 200 more row'>200+</button>
        <button class="btn1" id="decrement" title='less 200 more row'>200-</button>

      </div>
      <div class="form-row rowData">
        <!-- First Dropdown -->

        <div class="dropdown" id="dropdown1">

          <label class="dropdownBtn" id="dropdownBtn" onclick="toggleDropdown(event)"> Tissues</label>
          <div id="dropdownContent1" class="dropdown-content">
            <label><a style="text-align: left;" href="#" id="unselectAll">Unselect All</a></label>

            <label><input type="checkbox" value="Bone">Bone</label>
            <label><input type="checkbox" value="Skin">Skin</label>
            <label><input type="checkbox" value="Central Nervous System">Central Nervous System</label>
            <label><input type="checkbox" value="Lung" checked>Lung</label>
            <label><input type="checkbox" value="Peripheral Nervous System">Peripheral Nervous System</label>
            <label><input type="checkbox" value="Soft Tissue">Soft Tissue</label>
            <label><input type="checkbox" value="Esophagus">Esophagus</label>
            <label><input type="checkbox" value="Breast" checked>Breast</label>
            <label><input type="checkbox" value="Head and Neck">Head and Neck</label>
            <label><input type="checkbox" value="Haematopoietic and Lymphoid">Haematopoietic and Lymphoid</label>
            <label><input type="checkbox" value="Bladder">Bladder</label>
            <label><input type="checkbox" value="Kidney">Kidney</label>
            <label><input type="checkbox" value="Pancreas">Pancreas</label>
            <label><input type="checkbox" value="Large Intestine">Large Intestine</label>
            <label><input type="checkbox" value="Ovary" checked>Ovary</label>
            <label><input type="checkbox" value="Stomach">Stomach</label>
            <label><input type="checkbox" value="Biliary Tract">Biliary Tract</label>
            <label><input type="checkbox" value="Small Intestine">Small Intestine</label>
            <label><input type="checkbox" value="Placenta">Placenta</label>
            <label><input type="checkbox" value="Prostate">Prostate</label>
            <label><input type="checkbox" value="Testis">Testis</label>
            <label><input type="checkbox" value="Uterus">Uterus</label>
            <label><input type="checkbox" value="Vulva">Vulva</label>
            <label><input type="checkbox" value="Thyroid">Thyroid</label>
            <label><input type="checkbox" value="Lymphoid">Lymphoid</label>
            <label><input type="checkbox" value="Endometrium">Endometrium</label>
            <label><input type="checkbox" value="Cervix">Cervix</label>
            <label><input type="checkbox" value="Liver">Liver</label>
            <label><input type="checkbox" value="Adrenal Gland">Adrenal Gland</label>
            <label><input type="checkbox" value="Bowel">Bowel</label>
            <label><input type="checkbox" value="Unknown">Unknown</label>
            <!-- Add more options as needed -->
          </div>
          <div class="alert-message alert2  " style="position: absolute; top: 80px; " id="dp1">
            <span class="alert alert-danger">please select option</span>
          </div>
        </div>
        <!-- here is second-->

        <div class="dropdown" id="dropdown2">

          <label tyle=" min-width: 120px;" class="dropdownBtn" id="dropdownBtn2" onclick="toggleDropdown2(event)"> Max clinical phase</label>
          <div id="dropdownContent2" class="dropdown-content">
            <label><a style="text-align: left;" href="#" id="unselectAll2">Unselect All</a></label>

            <label><input type="checkbox" value="Approved">Approved drugs</label>
            <label><input type="checkbox" value="Phase I">Phase I</label>
            <label><input type="checkbox" value="Phase II">Phase II</label>
            <label><input type="checkbox" value="Phase III">Phase III</label>
            <label><input type="checkbox" value="Preclinical">Preclinical</label>
            <label><input type="checkbox" value="Unknown">Unknown</label>
            <!-- Add more options as needed -->
          </div>
          <div class="alert-message alert2 " style="position: absolute; top: 80px; " id="dp2">
            <span class="alert alert-danger">please select option</span>
          </div>

        </div>

        <div class="dropdown" id="dropdown3">

          <label class="dropdownBtn" style=" min-width: 120px;" id="dropdownBtn4" onclick="toggleDropdown4(event)">Data platform</label>
          <div id="dropdownContent4" class="dropdown-content">
            <label><a style="text-align: left;" href="#" id="unselectAll3">Unselect All</a></label>

            <label><input type="checkbox" value="GDSC1" checked>GDSC1</label>
            <label><input type="checkbox" value="GDSC2">GDSC2</label>
            <label><input type="checkbox" value="CCLE_NP24">CCLE_NP24</label>
            <label><input type="checkbox" value="NCI-60">NCI-60</label>
            <label><input type="checkbox" value="gCSI">gCSI</label>
            <label><input type="checkbox" value="FIMM">FIMM</label>
            <!-- Add more options as needed -->
          </div>

          <div class="alert-message alert2  " style="position: absolute; top: 80px; " id="dp3">
            <span class="alert alert-danger">please select option</span>
          </div>

        </div>
        <!-- 5th dropdown  -->
        <div class="dropdown" id="dropdown5">

          <label style=" min-width: 86px;" class="dropdownBtn" id="dropdownBtn5" onclick="toggleDropdown5(event)">PIC50</label>
          <div id="dropdownContent5" class="dropdown-content">
            <label><input type="checkbox" value="4">4-9</label>
            <label><input type="checkbox" value="5">5-9</label>
            <label><input type="checkbox" value="6">6-9</label>
            <label><input type="checkbox" value="7">7-9</label>
            <label><input type="checkbox" value="8">8-9</label>
            <!-- Add more options as needed -->
          </div>

          <div class="alert-message alert2  " style="position: absolute; top: 80px; " id="dp5">
            <span class="alert alert-danger">please select option</span>
          </div>

        </div>

        <!-- forth Dropdown -->
        <div class="dropdown" id="dropdown4" style=" z-index:3">

          <label class="dropdownBtn" id="dropdownBtn3" onclick="toggleDropdown3(event)">Cell line lineage</label>
          <div id="dropdownContent3" class="dropdown-content">
            <!-- Add more options as needed -->
            <input type="text" id="searchInput" onkeyup="filterOptions()" placeholder="Search...">
            <label><a style="text-align: left;" href="#" id="unselectAll4">Unselect All</a></label>

          </div>
          <div class="alert-message alert2 " style="position: absolute; top: 80px; " id="dp4">
            <span class="alert alert-danger">please select option</span>
          </div>

        </div>
        <!-- sixth Dropdown -->
        <div class="dropdown" id="dropdown6" style=" z-index:3 ; ">

          <label class="dropdownBtn" id="dropdownBtn6" onclick="toggleDropdown6(event)">Disease class</label>
          <div id="dropdownContent6" class="dropdown-content">
            <label><a style="text-align: left;" href="#" id="unselectAll5">Unselect All</a></label>

            <!-- Add more options as needed -->

          </div>
          <div class="alert-message alert2 " style="position: absolute; top: 80px; " id="dp4">
            <span class="alert alert-danger">please select option</span>
          </div>

        </div>

        <!-- seventh Dropdown -->
        <div class="dropdown" id="dropdown6" style=" z-index:3 ; ">

          <label class="dropdownBtn" id="dropdownBtn7" onclick="toggleDropdown7(event)">Compound class</label>
          <div id="dropdownContent7" class="dropdown-content">
            <label><a style="text-align: left;" href="#" id="unselectAll6">Unselect All</a></label>

            <!-- Add more options as needed -->

          </div>
          <div class="alert-message alert2 " style="position: absolute; top: 80px; " id="dp4">
            <span class="alert alert-danger">please select option</span>
          </div>

        </div>


        <!-- button  -->
      </div>
      <div style="display : flex">

        <button style="display: none;" disabled class="btn btn-success" id="openButton" onclick="toggleDiv()">Predict</button>
        <button class="btn btn-success" onclick="tableData()"><img width="30px" height="30px" src="images/tableimg_white.png" alt=""></button>
        <button class="btn btn-success" id="submitButton" type='submit' style="width:7rem">
          Apply Filter</button>

      </div>
    </form>
    <!-- end of the navbar -->
    <main class="graph_div  flex  col-12 col-sm-12  " id="div2">
      <!-- here is the disease legend  -->
      <div style=" width:15%">

        <div class="legend1" id="legend1" style=" margin-left: 12px">

          <legend class="legenddata" id="Drug_disease_phase">Disease clinical phase</legend>
          <ul id="phases_disease" class="legend_inner"></ul>
          <legend class="legenddata " id="Disease_class_heading">Disease class </legend>
          <ul id="disease_Class" class="legend_inner"></ul>
        </div>

      </div>

      <svg id="forcenetwork" width="100%" style="
               display: flex;
               justify-content: center;
               align-items: center;
               height:100%; 
               width:60%;" class=" forcenetwork  ">
        <!-- Loader embedded inside SVG -->
      </svg>
      <div id="loader_id">
        <div class="loader" id="loader"></div>
      </div>


      <div class="wrapper" id='wrapper'>
        <header style="justify-content: space-between;">
          <div style="display: flex;     justify-content: space-between; ">

            <button class="fitlerbtn" onclick="toggleDialog()" title="Filter specific Compounds and Celline">Filter Compounds/Celline</button>
            <div>
              <button style="" disabled class="btn btn-success" id="openButton" onclick="toggleDiv()">Predict</button>

            </div>

          </div>

          <!-- heading  -->
          <div>
            <p>Drug response (pIC50)</p>
          </div>


          <div id="dialog-container" style='max-width:500px; min-width: 350px; left:18%; top: 68%; '>
            <div id="dialog-header">
              <button onclick="toggleDialog2()" class="close-btn-search" style="background:none   ;  position: absolute;
                top: 10px;right: 3px;cursor: pointer;max-height: 100px;overflow: auto;
"><img height="20px" width="20px" src="images/icons8-close-60.png" alt=""></button>
              <!-- heading  -->
              <p>Filter Compounds/Celline</p>
            </div>

            <div class="filter_cell_cmd">
              <!-- compound filteration -->
              <div>
                <label for="search-bar">Search compounds:</label>
                <input type="text" id="search-bar" oninput="filterNames('name-list')" onclick="focusSearch('search-bar')">
                <a href="javascript:void(0)" id="selectAllLink" onclick="toggleCheckboxes('name-list' ,'selectAllLink')">UnselectAll</a>
                <ul id="name-list">
                </ul>

                <p id="no-matches" style="display: none;">No match found</p>
              </div>
              <!-- cellline filteration -->
              <div>
                <label for="search-bar2">Searh Cell lines:</label>

                <input type="text" id="search-bar2" oninput="filterNames2('name-list2')" onclick="focusSearch('search-bar2')">
                <a href="javascript:void(0)" id="selectAllLink2" onclick="toggleCheckboxes('name-list2' ,'selectAllLink2')">UnselectAll</a>

                <ul id="name-list2">
                </ul>

                <p id="no-matches2" style="display: none;">No match found</p>
              </div>

            </div>

            <div style="margin: 14px 0px 9px;width: 100%;justify-content: center;align-items: center;display: flex;">

              <button class="sliderbtn" onclick="saveNames()">Filter</button>
            </div>
          </div>

        </header>
        <div class="price-input">
          <div class="field">
            <span>Min</span>
            <input type="number" class="input-min" value="6.0" step="0.1">
          </div>
          <div class="separator">-</div>
          <div class="field">
            <span>Max</span>
            <input type="number" class="input-max" value="9.0" step="0.1">
          </div>
        </div>
        <div class="slider">
          <div class="progress"></div>
        </div>
        <div class="range-input">
          <input id="min_slider" type="range" class="range-min" min="4.0" max="9.0" step="0.1" value="6.0">
          <input id="max_slider" type="range" class="range-max" min="4.0" max="9.0" step="0.1" value="9.0">
        </div>
        <div class="legend" id="legend_main2">
          <div style="width :40%">
            <legend class="legenddata" id="max_clinical_phase">Drug's max clinical phase</legend>
            <ul id="myList" class="legend_inner"></ul>
            <legend class="legenddata" id="Data_platform">Data platform</legend>
            <ul id="dataset" class="legend_inner"></ul>
            <legend class="legenddata" id="Metric">Metric</legend>
            <ul id="matric_set" class="legend_inner"></ul>
          </div>
          <div style="width : 60%">
            <legend class="legenddata" id="Tissue">Tissue</legend>
            <ul id="child_node" class="legend_inner"></ul>
          </div>
        </div>
      </div>
    </main>
    <!-- second slider and btns  -->

    <footer class="sliderpart2" id='buttonbar' style="justify-content: start;">

      <div class='alignitems'>
        <div style="margin-top : 15px">
          <p style="margin-bottom: 0;">Total compounds visible: <span id="parent_count"></span></p>
          <p>Total cell line visible: <span id="child_count"></span> </p>
        </div>
        <button class="sliderbtn " id="zoom-in-button">zoom-in</button>
        <button class="sliderbtn " id="zoom-out-button">zoom out</button>
        <div class="slider2size">
          <div style="display: flex;margin-bottom: -9px;">
            <p style="display: none;" id="rangeValue">50 </p>
            <p id="parent_count2">20</p>
            <p>Connected compounds</p>
          </div>
          <input id="nodeCountSlider2" type="range" min="0" max="100" value="50" />

        </div>
        <!-- btntag -->
        <button class="sliderbtn" id="redraw" title="move the nodes to tis default position">redraw</button>
        <button class="sliderbtn " id="export" title="(PNG , JPEG , XLSX)">Export</button>

      </div>
    </footer>

    <div class="card" id="cardid" style="display: none; z-index:9999">
      <p class="cl-picker">change color</p>
      <ul class="ul_of_color_picker" id="colorList">

      </ul>
    </div>


  </div>
  </div>
  <div id="applyfilter">
    <span class="alertfilter">filtering applied </span>
  </div>


  <!-- overlay  -->





  <section style="background-color : white;  z-index : 5">
    <span class='overlay'></span>
    <div class="modal-box">
      <div class="model_box_inner">
        <!-- heading  -->
        <p>Export Chart as</p>

        <div class="buttons exportbtn">

          <button class="png" id='png'> Download PNG </button>
          <button class="jpeg" id="jpeg"> Download JPEG </button>
          <button class="csv" id="csv"> Download XLS </button>
          <!-- <button class="close-btn"> Close</button> -->

          <button class="close-btn" style="background:none   ;  position: absolute;
                top: 10px;right: 10px;cursor: pointer;max-height: 100px;overflow: auto;
"><img height="20px" width="20px" src="images/icons8-close-60.png" alt=""></button>

        </div>
      </div>


    </div>
  </section>


  <div class="blur_the_background">
    <div class="parent_description ">
      <!-- heading  -->
      <p id="drugname">name</p>
      <div class="container">

        <form class="toggle">

          <!-- <input type="radio" id="choice2" name="choice" value="productive" checked>
          <label for="choice2">Biologics Structure </label>
          <input type="radio" id="choice1" name="choice" value="creative">
          <label for="choice1">Properties</label> -->

        </form>
      </div>
      <div class="table-container">
        <table>
          <tbody id="compoundTableBody">
            <!-- Data will be dynamically inserted here using JavaScript -->
          </tbody>
          <!-- <img src="image_not_available.png" alt="Structure Image" class="structure-image"> -->
        </table>
      </div>
      <button style="background:none " id='parent_des_close'><img height="20px" width="20px" src="images/icons8-close-60.png" alt=""></button>

    </div>
  </div>


  <!-- here is the model to check the smiles  -->


  <div id="customOverlay" onclick="toggleDiv()"></div>

  <div id="customInteractiveDiv">
    <div>
      <p>Enter Smiles</p>
    </div>
    <button id="customCloseButton" onclick="toggleDiv()"><img height="20px" width="20px" src="images/icons8-close-60.png" alt=""></button>
    <textarea id="customSearchBar" class="search-bar3" placeholder="Enter the Smiles with the new line format:
  CCC1=C(C(=NC(=N1)N)N)C2=CC=C(C=C2)Cl
  CN1CCN(CCOc2cc(OC3CCOCC3)c3c(Nc4c(Cl)ccc5c4OCO5)ncnc3c2)CC1
  CCC1=C(C(=NC(=N1)N)N)C2=CC=C(C=C2)Cl
  "></textarea>
    <button class="sliderbtn" id="customSubmitButton" onclick="submitCommand()">Submit</button>
  </div>




  <script src="https://d3js.org/d3.v7.min.js"></script>
  <script src="https://d3js.org/d3-force.v3.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>


  <script src="js_scripts/smiles.js"></script>
  <!---Script to fetch data  from php script --->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- here is the script of the table to be load  -->
  <script src="js_scripts/dropdown_Code.js"></script>
  <!-- JavaScript for handling form submission and AJAX -->
  <script src="js_scripts/Get_the_data.js"></script>
  <script src="js_scripts/filter_single_code_by_search.js"></script>
  <script src="js_scripts/legendFunction.js"></script>
  <script>
    let nodes = []; // unique nodes   
    let links = []; // links  from the json files
    let node = []; // DOM element    
    let link = []; // DOM ELEMENT 

    let filteredLinks = [];
    let filterNodes = [];
    let listItems = []; // onclick on the legend 
    let max_phase_cliked = [];


    let clicked;
    let list_hidden = [];
    let remove_maxPhase = [];

    //  onclick data set 

    let dataSet_link = []
    let clicked2;
    let list_hidden_dataset = [];

    //  onclick child nodes 
    let dataSet_child = []
    let clicked3;
    let list_hidden_childnode = [];
    let child_categories_border;
    //  onclick matric 
    let matric_link = [];

    let minValue = 6;
    let maxValue;

    let healthCategoriesWithColors;

    let slider_range = 100;
    const slider2 = document.getElementById("nodeCountSlider2");
    // onclick dataset   

    let list_hidden_links = [];

    let jsondata2;

    let csvfile = [];
    let response;
    let create_it = true;


    let visible_node = [];
    let visible_parentnode = [];
    let visible_childnode = [];


    let simulation;

    let drug_des_parent;
    let clickedData;
    let name_of_drug;
    // fetching the json file  
    let curentnodes = 400;
    // legend entry 
    let colors;
    let child_colors;

    // max_phses 
    let phases = [];
    let max_phase_categories;


    // dataset entry 
    let dataset_legend = [];
    let data_Set;

    // matric entry

    let matric_legend = [];
    let matric_categories;
    let matric_legend_data = []
    // phase disease entry 


    let phase_legend_data = ["Preclinical", "Phase 1", "Phase 2", "Phase 3", "Phase 4"];
    let phase_categories;


    //  childnode entry 
    let ONCOTREE_LINEAGE_legend = [];
    let child_categories;
    const ONCOTREE_LINEAGE_Data = [

      'Lymphoid', 'Endometrium',
      'Bone', 'Skin', 'Central Nervous System', 'Lung', 'Peripheral Nervous System', 'Soft Tissue', 'Esophagus',
      'Breast',
      'Head and Neck',
      'Haematopoietic and Lymphoid',
      'Bladder', 'Kidney', 'Pancreas',
      'Large Intestine',
      'Ovary', 'Stomach',
      'Biliary Tract', 'Small Intestine',
      'Placenta',
      'Prostate',
      'Testis',
      'Uterus',
      'Vulva',
      'Thyroid',
      'Unknown'
    ];

    // disease_class entry 
    let disease_Class_legend = [];
    let disease_categories;
    const disease_Class_Data = [
      'Behavior mechanisms',
      'Cardiovascular',
      'Chemically-Induced disorders', 'Congenital and neonatal', 'Digestive system', 'Endocrine system', 'Eye', 'Female urogenital',
      'Genetic inborn',
      'Hemic and lymphatic', 'Immune system', 'Infections', 'Male urogenital',
      'Mental disorders', 'Musculoskeletal', 'Neoplasm',
      'Nervous system', 'Nutritional and Metabolic',
      'Occupational diseases', 'Otorhinolaryngologic',
      'Pathological conditions', 'Respiratory tract',
      'Skin and connective tissue', 'Stomatognathic', 'Wounds and injuries'
    ];

    // here are the variable of removing and showing the node of parent compound 

    let hidden_compound = [];


    // disease_class entry ended 

    let not_remove = true;
    let count_increment = 1;

    document.getElementById("increment").addEventListener("click", function(event) {
      event.preventDefault();
      disableButtons("increment");
      disableButtons("decrement");
      count_increment += 1;
      ajax();
    });

    document.getElementById("decrement").addEventListener("click", function(event) {
      event.preventDefault();
      if (count_increment > 1) {
        disableButtons("decrement");
        disableButtons("increment");
        count_increment -= 1;
        ajax();
      } else {
        alert("Minimum data fetched");
      }
    });

    function disableButtons(buttonId) {
      let buttonDisable = document.getElementById(buttonId);
      buttonDisable.disabled = true;

      let originalColor = buttonDisable.style.backgroundColor;
      let originalText = buttonDisable.innerHTML;

      // Change the background color
      buttonDisable.style.backgroundColor = "#ccc";
      buttonDisable.innerHTML = "Wait";

      // Disable the other button
      let otherButtonId = buttonId === "increment" ? "decrement" : "increment";
      let otherButton = document.getElementById(otherButtonId);
      otherButton.disabled = true;

      // Enable both buttons and restore the original background color after 5 seconds
      setTimeout(function() {
        buttonDisable.disabled = false;
        buttonDisable.style.backgroundColor = originalColor;
        buttonDisable.innerHTML = originalText;

        // Enable the other button
        otherButton.disabled = false;
      }, 5000);
    }


    async function fetchData(data) {
      try {
        response = data;


        processData(response);
      } catch (error) {
        console.error("Error loading the JSON file:", error);

      }
    }
    // fetching the data ended here 
    let i = 0;

    function processData(data) {

      const uniqueProteins = new Set();
      data.forEach((item) => {
        if (!uniqueProteins.has(item.COMPOUND_NAME)) {
          uniqueProteins.add(item.COMPOUND_NAME);


          nodes.push({
            id: item.COMPOUND_NAME,
            type: "parentnode",
            MAX_PHASE: item.MAX_PHASE,
            flag: true
          });

        }

      })
      data.forEach((item) => {
        if (!uniqueProteins.has(item.CELL_LINE_NAME)) {
          uniqueProteins.add(item.CELL_LINE_NAME);

          nodes.push({
            id: item.CELL_LINE_NAME,
            type: "childnode",
            MAX_PHASE: item.MAX_PHASE,
            oncotree_change: item.ONCOTREE_LINEAGE,
            dataset: item.DATASET,
            Disease_class: "temp",
            phase: "temp"
          });


        }
      });

      data.forEach((item) => {
        if (!uniqueProteins.has(item.Disease_name)) {
          uniqueProteins.add(item.Disease_name);
          item.Phase === 'Preclinical' ? console.log(item.Phase) : null;

          nodes.push({
            id: item.Disease_name,
            type: "diseasenode",
            MAX_PHASE: "temp2",
            oncotree_change: item.Disease_class,
            dataset: "temp",
            Disease_class: item.Disease_class,
            phase: item.Phase === 'Preclinical' ? item.Phase : `Phase ${item.Phase}`

          });

        }
      });

      //  creating the links  

      links = data.flatMap((item) => [{
          source: item.COMPOUND_NAME,
          target: item.CELL_LINE_NAME,
          value: item.VALUE,
          max_range_link: item.MAX_PHASE,
          dataset: item.DATASET,
          link_matric: item.METRIC,
        },
        {
          source: item.COMPOUND_NAME,
          target: item.Disease_name,
          // phase: item.Phase,
          value: "temp",
          max_range_link: "temp2",
          dataset: "temp3",
          link_matric: item.Phase === 'Preclinical' ? item.Phase : `Phase ${item.Phase}`,
        },
      ]);
      console.log("nodes", nodes)
      console.log("links", links);

      let tempdata = [];

      data.filter(data => {
        if (!tempdata.includes(data.ONCOTREE_LINEAGE))
          tempdata.push(data.ONCOTREE_LINEAGE)
      })
    }
    // custom drag function

    function customDrag(simulation) {
      function dragstarted(event, d) {
        // if (d !== selectedNode) return; // Allow dragging only for the selected node
        if (!event.active) simulation.alphaTarget(0.3).restart();
      }

      function dragged(event, d) {
        // if (d !== selectedNode) return; // Allow dragging only for the selected node
        d.fx = event.x;
        d.fy = event.y;
      }

      function dragended(event, d) {
        // if (d !== selectedNode) return; // Allow dragging only for the selected node
        if (!event.active) simulation.alphaTarget(0);
      }

      return d3
        .drag()
        .on("start", dragstarted)
        .on("drag", dragged)
        .on("end", dragended);
    }


    // custom drag function ended



    // here the function to fetch the data from the databse for the biologics description 
    // generate_table

    function fetchData2(drugName) {
      // Make AJAX request to PHP script
      $.ajax({
        type: "POST",
        url: "",
        data: {
          drugName: drugName
        },
        success: function(data) {
          drug_des_parent = JSON.parse(data);

          generate_table();
          // You can do further processing here
        },
        error: function(xhr, status, error) {
          console.error("Error: " + error);
        }
      });
    }


    function fetchData3(drugName2) {
      // Make AJAX request to PHP script
      $.ajax({
        type: "POST",
        url: "",
        data: {
          drugName2: drugName2
        },
        success: function(data) {
          // Handle the returned data
          drug_des_parent = JSON.parse(data);

          generate_table();
          // You can do further processing here
        },
        error: function(xhr, status, error) {
          console.error("Error: " + error);
        }
      });
    }

    function generate_table() {

      var globalCode;
      var div = document.querySelector('.parent_description');
      div.classList.toggle('show');
      var div = document.querySelector('.blur_the_background');
      div.classList.toggle('show');

      var name = document.querySelector('#drugname');
      name.innerHTML = clickedData.id;

      let dataobject = drug_des_parent['0'];
      // Function to populate the table
      function populateTable() {
        const tableBody = document.getElementById('compoundTableBody');


        tableBody.innerHTML = '';

        Object.entries(dataobject).forEach(([key, value]) => {
          let keyCell = key;
          let valueCell = value;
          if (keyCell === 'TARGETS_UNIPROT') {

            globalCode = valueCell;

          }
        });

        var keyToRemove = "TARGETS_UNIPROT";

        // Remove the key-value pair
        delete dataobject[keyToRemove];



        Object.entries(dataobject).forEach(([key, value]) => {

          // Skip creating a row for the keys 'COMPOUND_ID', 'PREFERRED_COMPOUND_NAME', and 'Source_DB_DR_ID'
          if (key === 'COMPOUND_id' || key === 'PREFERRED_COMPOUND_NAME' || key === 'Source_DB_DR_ID') {
            return; // Skip to the next iteration of the loop
          }
          const row = document.createElement('tr');

          const keyCell = document.createElement('td');
          keyCell.textContent = key;
          row.appendChild(keyCell);

          const valueCell = document.createElement('td');
          row.appendChild(valueCell);
          valueCell.textContent = value;

          // to the top 
          keyCell.style.whiteSpace = 'nowrap';
          keyCell.style.verticalAlign = 'top';
          if (keyCell.innerText === 'CROSS_REFERENCES_CELL_LINES') {
            let text_change = valueCell.innerHTML;
            var formattedData = formatData(text_change);
            // Use innerHTML instead of textContent to render HTML tags
            valueCell.innerHTML = formattedData;

            keyCell.innerHTML = 'Cross reference cell lines'
          } else if (keyCell.innerText === 'COMMENTS') {
            let text_change = valueCell.innerHTML;
            var formattedData = formatData2(text_change);
            // Use innerHTML instead of textContent to render HTML tags
            valueCell.innerHTML = formattedData;

            keyCell.innerHTML = 'Comments'
          } else
          if (keyCell.innerText === 'REFERENCE_ID') {

            let text_change = valueCell.innerHTML;
            var formattedData = formatData3(text_change);
            // Use innerHTML instead of textContent to render HTML tags
            valueCell.innerHTML = formattedData;
            keyCell.innerText
            keyCell.innerHTML = 'Reference ID'
          } else if (keyCell.innerHTML === 'TARGETS') {

            let text_change = valueCell.innerHTML;



            var formattedData = formatData4_compound(text_change, globalCode)
            valueCell.innerHTML = formattedData;
            keyCell.innerHTML = 'Targets'
          } else if (keyCell.innerHTML === 'COMPOUND_NAME') {

            keyCell.innerHTML = 'Compound name'
          } else if (keyCell.innerHTML === 'PUBCHEM_ID') {

            keyCell.innerHTML = 'PubChem ID'
            valueCell.innerHTML = Math.floor(value);

          } else if (keyCell.innerHTML === 'CHEMBL_ID') {
            if (valueCell.innerHTML === "0") {
              valueCell.innerHTML = ""
            }
            keyCell.innerHTML = 'ChEMBL ID'

          } else if (keyCell.innerHTML === 'MAX_PHASE') {

            keyCell.innerHTML = ' Max clinical phase'

          } else if (keyCell.innerHTML === 'SMILES') {

            keyCell.innerHTML = 'SMILE'

          } else if (keyCell.innerHTML === 'COMPOUND_CLASS') {

            keyCell.innerHTML = 'Compound class'

          } else if (keyCell.innerHTML === 'INCHI_KEY') {

            keyCell.innerHTML = 'Standard InChiKey'

          } else if (keyCell.innerHTML === 'INCHI_KEY') {

            keyCell.innerHTML = 'Standard InChiKey'

          } else if (keyCell.innerHTML === 'CELL_LINE_NAME') {

            keyCell.innerHTML = 'Celline name'

          } else if (keyCell.innerHTML === 'CELL_LINE_SYNONYM') {

            keyCell.innerHTML = 'Celline synonym'

          } else if (keyCell.innerHTML === 'COSMIC_ID') {

            keyCell.innerHTML = 'Cosmic ID'

          } else if (keyCell.innerHTML === 'SANGER_MODEL_ID') {

            keyCell.innerHTML = 'Sanger model ID'

          } else if (keyCell.innerHTML === 'Source_DB_CL_ID') {

            keyCell.innerHTML = 'Source DB CL ID'

          } else if (keyCell.innerHTML === 'TCGA_STUDY_CODE') {

            keyCell.innerHTML = 'TCGA study code'

          } else if (keyCell.innerHTML === 'ONCOTREE_CODE') {

            keyCell.innerHTML = 'Oncotree code'

          } else if (keyCell.innerHTML === 'ONCOTREE_LINEAGE') {

            keyCell.innerHTML = 'Oncotree lineage'

          } else if (keyCell.innerHTML === 'ONCOTREE_PRIMARY_DISEASE') {

            keyCell.innerHTML = 'Oncotree primary disease'

          } else if (keyCell.innerHTML === 'CELLOSAURUS_DISEASE') {

            keyCell.innerHTML = 'Cellosaurus disease'

          }




          tableBody.appendChild(row);
        });
      }

      function formatData(data) {
        var lines = data.split('|');
        var formattedLines = [];

        for (var i = 0; i < lines.length - 1; i++) {
          var parts = lines[i].split(';');
          var formattedText = '<b>' + parts[0] + '</b>' + '<a href="https://cancer.sanger.ac.uk/cosmic" target="_blank">' + parts[1] + '</a>';
          formattedLines.push(formattedText);
        }


        return formattedLines.join('<br>');
      }

      function formatData2(data) {
        var lines = data.split('|');
        var formattedLines = [];


        for (var i = 0; i < lines.length - 1; i++) {
          var parts = lines[i].split(':');
          var formattedText = '<b>' + parts[0] + '</b>' + ':' + parts[1];
          formattedLines.push(formattedText);
        }



        return formattedLines.join('<br>');
      }

      function formatData3(data) {

        var lines = data.split('|');
        var formattedLines = [];

        for (var i = 0; i < lines.length - 1; i++) {
          var parts = lines[i].split('=');
          var formattedText = '<b>' + parts[0] + '</b>' + '=' + parts[1];
          formattedLines.push(formattedText);
        }



        return formattedLines.join('<br>');
      }

      function formatData4_compound(data, data2) {
        var lines = data.substring(1).split(', ');
        var lines2 = data2.split(', ')
        var formattedLines = [];

        console.log(data, "data data2", data2)

        for (var i = 0; i < lines.length; i++) {
          var parts = lines[i].substring(1).split(' (PChEMBL=');
          var entityName = parts[0];
          var pChembl = parts[1].slice(0, -1); // Removing the closing parenthesis


          var formattedText = '<a href="https://www.uniprot.org/uniprotkb/' + lines2[i].substring(1, lines2[i].length - 1) + '/entry"target="_blank">' + entityName + '</a> (PChEMBL=' + pChembl + ',';

          formattedLines.push(formattedText);

        }

        return formattedLines.join('<br>');

      }

      // Call the function to populate the table
      populateTable();
      // const toggleForm = document.querySelector('.toggle');
      const compoundTable = document.querySelector('table');

    }

    //  initialize the graph for the first time  


    const svg = d3.select("#forcenetwork");
    const svgWidth = +svg.node().getBoundingClientRect().width - 100;
    const svgHeight = +svg.node().getBoundingClientRect().height - 100;



    function force_network_grapgh() {
      // tag


      let bodyElement = document.body;
      let y_graph = bodyElement.clientHeight / 2 - 90;
      let x_graph = bodyElement.clientWidth / 2 - 300;

      checkbox_names = [];
      checkbox_saves = [];
      checkbox_saves_child = [];

      function calculateDistance(link, index, count) {
        // Return distance based on the index

        if (index % 2 === 0 && count > 1) {
          if (link.value > 7) {
            return link.value * 35;

          } else {
            return link.value * 50;

          }
          // Even index links have a distance of 200
        } else if (index % 2 === 0 && count === 1) {

          return link.value * 25;
        } else {
          // Odd index links have a distance of 100
          return 400;
        }
      }

      const g = svg.append("g");
      // simulationtag
      const parentNodeCount = nodes.filter(node => node.type === "parentnode").length;
      console.log(parentNodeCount, 'here is the parent count');
      // here is the start of the simulation 
      simulation = d3
        .forceSimulation(nodes)
        .force(
          "link",
          d3.forceLink(links)
          .id((d) => d.id)
          // .distance(link => link.value * 200 ))

          .distance((link, index) => calculateDistance(link, index, parentNodeCount))
          //       .distance(function(d){
          //     return (Math.random() * (500) + 2);

          // })
        )

        // .force("charge", d3.forceManyBody().strength(-100))
        // .force("x", d3.forceX(x_graph))
        // .force("y", d3.forceY(y_graph))
        .force("center", d3.forceCenter(x_graph, y_graph))
      // .force('collision', d3.forceCollide().radius(15)); // Adjust the radius as needed
      legendinfo();
      // Manually set colors based on the dataset value
      link = g
        .selectAll(".link")
        .data(links)
        .enter().append("line")
        .style("stroke", function(d) {
          switch (d.dataset) {
            case "GDSC1":
              return "#4372c4";
            case "GDSC2":
              return "#fe0000";
            case "CCLE_NP24":
              return "#9B35C8";
            case "NCI-60":
              return "#0bc00f";
            case "gCSI":
              return "#fe8f01";
            case "FIMM":
              return "#f99cc8";

              // Default color if the dataset doesn't match any specific case

          }

          switch (d.link_matric) {
            case 'Preclinical':
              return "red";
            case 'Phase 1':
              // handle Phase 1
              return "blue"; // or any other color you desire
            case 'Phase 2':
              // handle Phase 2
              return "green"; // or any other color you desire
            case 'Phase 3':
              // handle Phase 3
              return "grey"; // or any other color you desire
            case 'Phase 4':
              // handle Phase 4
              return "orange"; // or any other color you desire
          }

        })
        .attr("stroke-width", function(d) {

          if (d.value < 5) {
            return 0.5;
          } else {
            return d.value - 4.5;
          }
        })
        .style("stroke-dasharray", function(d) {
          if (d.link_matric === 'pIC50') {
            return "2,2";
          } else if (d.link_matric === 'pEC50') {
            return "5,5";
          } else if (d.link_matric === 'pGI50') {
            return "0";
          }
        })
        .attr("x1", function(d) {
          return d.source.x;
        })
        .attr("y1", function(d) {
          return d.source.y;
        })
        .attr("x2", function(d) {
          return d.target.x;
        })
        .attr("y2", function(d) {
          return d.target.y;
        }).on("mouseover", function(d) {


          let r = event.target.__data__;

          tooltip2.transition()
            .style("opacity", 0.9);
          tooltip2.html("<strong>Link Value:</strong> " + r.value)
            .style("left", d.pageX + "px")
            .style("top", d.pageY + "px");
        })
        .on("mouseout", function(d) {
          tooltip2.transition()
            .duration(500)
            .style("opacity", 0);
        });
// here is the update there there 

console.log(link ,'here are the links d3 link ')

let foundLinks = [];

// Iterate over the pairs in _groups[0]
for (let i = 0; i < link._groups[0].length - 1; i++) {

    let link1 = link._groups[0][i].__data__;
    let link2 = link._groups[0][i + 1].__data__;
console.log(link1 , 'here is the link1')
    if (link1.source.id === link2.source.id && link1.target.id === link2.target.id) {
        foundLinks.push(link1);
    }
}

// Output the found links
console.log(foundLinks);


      // Define a tooltip div with class "tooltip2"
      var tooltip2 = d3.select("body").append("div")
        .attr("class", "tooltip2")
        .style("opacity", 0);

      node = g
        .selectAll(".node")
        .data(nodes)
        .enter()
        .append("g")
        .attr("class", "node").call(customDrag(simulation));

      node
        // .on("click", )

        .on("click", handleDblClick)
        .on("contextmenu", handleClick);


     

      let parentnodes2 = node.filter(function(node) {
        if (node.type === "parentnode") {
          return node;
        }
      })

      slider2.max = parentnodes2.size();
      slider2.value = parentnodes2.size();
      const rangetext = document.getElementById("rangeValue");

      rangetext.textContent = parentnodes2.size();

      let child = 0;
      let parent = 0;
      let ratio = 0;


      node.filter(function(node) {

        if (node.type === "childnode") {
          child = child + 1;
        }
        if (node.type === "parentnode") {
          parent = parent + 1;
        }
      })

      if (child > parent) {
        ratio = child / (child + parent) * 100
      } else if (child < parent) {
        ratio = parent / (child + parent) * 100
      }


      if (ratio > 80) {
        simulation.force("charge", d3.forceManyBody().strength(-50))
      } else {
        simulation.force("charge", d3.forceManyBody().strength(-50))

      }
      var linksHidden = true;


      // tag1  

      function handleClick(event) {
        event.preventDefault();
        clickedData = event.target.__data__;
        name_of_drug = clickedData.id;
        var index = hidden_compound.indexOf(name_of_drug);
        if (index === -1) { // Element not found in the array
          hidden_compound.push(name_of_drug);
        } else { // Element found in the array
          hidden_compound.splice(index, 1); // Remove the element from the array
        }
        range_of_links(minValue, maxValue, slider_range);


      }
      //  handle the double click here 

      function handleDblClick(event, d) {
        event.preventDefault();
        clickedData = event.target.__data__;
        name_of_drug = clickedData.id;
        let compoundData = {
          "COMPOUND_NAME": "Compound1",
          "PREFERRED_COMPOUND_NAME": "Preferred1",
          "PUBCHEM_ID": 1,
          "CHEMBL_ID": "ChEMBL1",
          "MAX_PHASE": 2,
          "Source_DB_DR_ID": 101
        };
        // call the function 



        if (clickedData.type === "parentnode") {


          fetchData2(name_of_drug);

        } else if (clickedData.type === "childnode") {

          fetchData3(name_of_drug);

        }

        var closeButton = document.getElementById('parent_des_close');
        closeButton.addEventListener('click', function() {

          // document.getElementById('choice2').checked = true;
          // document.getElementById('choice1').checked = false;

          // const structureImage = document.querySelector('.structure-image');
          // structureImage.style.display = 'block'; // Show the image
          var div = document.querySelector('.parent_description');
          div.classList.remove('show');
          var div = document.querySelector('.blur_the_background');
          div.classList.remove('show');
        });
      }

      //  create childnode here

      let degree;
      let min_degree = 500;
      let max_degree = 0;
      let x_value;
      let linksize;
      let visiblenode = []

      //  here we are calculating the link 

      function calculateLinkSize(d) {
        const degree = links.filter(
          (link) => link.source.id === d.id || link.target.id === d.id
        ).length;

        if (max_degree < degree) {
          max_degree = degree;
        }

        if (min_degree > degree) {
          min_degree = degree;
        }

        if (min_degree !== max_degree) {
          x_value = 8 / (max_degree - min_degree);
        }

        if (min_degree === max_degree) {
          return 2.5;
        } else {
          linksize = (degree - min_degree) * x_value + 2.5;
          return linksize;
        }
      }

      node.filter((d) => d.type === "childnode")
        .append("circle")
        .attr("r", function(d) {
          return calculateLinkSize(d);

        })
        .attr("fill", (d) => {
          let category = d.oncotree_change;
          let color;
          if (category === 'Bone') {
            color = child_colors[0];
          } else if (category === 'Skin') {
            color = child_colors[1];
          } else if (category === 'Central Nervous System') {
            color = child_colors[2];
          } else if (category === 'Lung') {
            color = child_colors[3];
          } else if (category === 'Peripheral Nervous System') {
            color = child_colors[4];
          } else if (category === 'Soft Tissue') {
            color = child_colors[5];
          } else if (category === 'Esophagus') {
            color = child_colors[6];
          } else if (category === 'Breast') {
            color = child_colors[7];
          } else if (category === 'Head and Neck') {
            color = child_colors[8];
          } else if (category === 'Haematopoietic and Lymphoid') {
            color = child_colors[9];
          } else if (category === 'Bladder') {
            color = child_colors[10];
          } else if (category === 'Kidney') {
            color = child_colors[11];
          } else if (category === 'Endometrium') {
            color = child_colors[12];
          } else if (category === 'Lymphoid') {
            color = child_colors[13];
          } else if (category === 'Adrenal Gland') {
            color = child_colors[14];
          } else if (category === 'Bowel') {
            color = child_colors[15];
          } else if (category === 'Pancreas') {
            color = child_colors[0]; // Repeat the color for category 11
          } else if (category === 'Large Intestine') {
            color = child_colors[1];
          } else if (category === 'Ovary') {
            color = child_colors[2];
          } else if (category === 'Stomach') {
            color = child_colors[3];
          } else if (category === 'Biliary Tract') {
            color = child_colors[4];
          } else if (category === 'Small Intestine') {
            color = child_colors[5];
          } else if (category === 'Placenta') {
            color = child_colors[6];
          } else if (category === 'Prostate') {
            color = child_colors[7];
          } else if (category === 'Testis') {
            color = child_colors[8];
          } else if (category === 'Uterus') {
            color = child_colors[9];
          } else if (category === 'Vulva') {
            color = child_colors[10];
          } else if (category === 'Thyroid') {
            color = child_colors[11];
          } else
          if (category === 'Cervix') {
            color = child_colors[12];
          } else if (category === 'Liver') {
            color = child_colors[13];
          } else {
            color = "black"
          }
          return color;
        })
        .attr("stroke",
          (d) => {
            if (child_categories_border.includes(d.oncotree_change)) {
              return "black";
            }
          })
        .attr("stroke-width", 3)



      node.filter(function(d) {
          return d.type === 'diseasenode';
        })
        .append("path")
        .attr("d", "M 0 0 L 8 16 L -8 16 Z") // Path data for a triangle
        .style("fill", function(d) {


          return healthCategoriesWithColors.find(category => category.category === d.Disease_class)?.color || "black";
        })


      node.filter((d) => d.type === "parentnode")
        .append("rect")
        .attr("width", 30) // Set the width of the rectangle
        .attr("height", 12) // Set the height of the rectangle
        .attr("fill", function(node) {
          if (node.MAX_PHASE === "Approved") {
            return "#0bc00f"; // Updated color for "Approved"
          } else if (node.MAX_PHASE === "Phase I") {
            return "#4372c4"; // Updated color for "Phase I"
          } else if (node.MAX_PHASE === "Phase II") {
            return "#fe0000"; // Updated color for "Phase II"
          } else if (node.MAX_PHASE === "Phase III") {
            return "#9B35C8"; // Updated color for "Phase III"
          } else if (node.MAX_PHASE === "") {
            return "#fe8f01"; // Updated color for empty string
          } else if (node.MAX_PHASE === "Unknown") {
            return "#fe8f01"; // Updated color for "Unknown"
          } else if (node.MAX_PHASE === "Preclinical") {
            return "#f99cc8"; // Updated color for "Preclinical"
          }
        })
        .attr("x", -12)
        .attr("y", -8)
        .attr("rx", 5) // Set the x-axis border radius
        .attr("ry", 5) // Set the y-axis border radius
        .attr("stroke", "#fff")
        .attr("stroke-width", 1.5);




      // Add tooltips
      let tooltip = node
        .append("text")
        .text((d) => d.id)
        .attr("dx", 6)
        .attr("dy", 0)
        .style("font-size", "12.208px").style("font-family", "Arial")
        .attr("text-anchor", "middle")
        .style("fill", "black")
        .style("z-index", 999)
        .style("opacity", (d) => ((d.type === "parentnode" && (d.MAX_PHASE === "" || d.MAX_PHASE === "Unknown")) ? 0 : 1)); // hide initially for specific nodes

      node.on("mouseover", handleMouseOver).on("mouseout", handleMouseOut);

      if (links.length > 1200) {
        tooltip.style("opacity", 0); // hide initially for specific nodes


      }



      tooltip.attr('dy', function(d) {
        if (d.type === "parentnode") {
          return `1.5rem`
        } else if (d.type === "childnode") {
          return calculateLinkSize(d) + 9;
        }
      });

      function handleMouseOver(d) {
        // Show tooltip only for the hovered node
        d3.select(this).select("text").style("opacity", 1);

        // You might want to adjust the tooltip position based on your visualization
        // tooltip.attr("x", d.x).attr("y", d.y);
      }


      function handleMouseOut(d) {
        // Hide tooltip when the mouse is out  
        if (links.length > 1200) {
          d3.select(this).select("text").style("opacity", 0)
        } else {
          d3.select(this).select("text").style("opacity", (d) => (d.MAX_PHASE === "" || d.MAX_PHASE === "Unknown") && d.type === "parentnode" ? 0 : 1);

        }
      }


// here is the updated code 


      simulation.on("tick", () => {
        link
          .attr("x1", (d) => Math.max(0, Math.min(svgWidth, d.source.x)))
          .attr("y1", (d) => Math.max(0, Math.min(svgHeight, d.source.y)) + 50)
          .attr("x2", (d) => Math.max(0, Math.min(svgWidth, d.target.x)))
          .attr("y2", (d) => Math.max(0, Math.min(svgHeight, d.target.y)) + 50);

        node.attr("transform", (d) => `translate(${Math.max(0, Math.min(svgWidth, d.x))}
        ,${Math.max(0, Math.min(svgHeight, d.y))+50})`);
      });
      
         
        
        
      var zoom = d3.zoom()
        .scaleExtent([0.5, 10]) // Set the zoom scale extent as needed
        .on("zoom", zoomed);

      g.call(zoom);

      function zoomed() {
        if (g) {
          // g.attr("transform", d3.event.transform);

          var transform = d3.zoomTransform(this);
          // Access the current zoom state using d3.zoomTransform
          // It returns an object with properties: k (scale), x (translateX), and y (translateY)

          // Apply the zoom transformation directly to the SVG elements
          g.attr("transform", "translate(" + transform.x + "," + transform.y + ") scale(" + transform.k + ")");
        }
      }
      var zoomInButton = document.getElementById("zoom-in-button");
      var zoomOutButton = document.getElementById("zoom-out-button");
      zoomInButton.addEventListener("click", function() {
        svg.transition().call(zoom.scaleBy, 1.2);
      });
      zoomOutButton.addEventListener("click", function() {
        svg.transition().call(zoom.scaleBy, 0.8);
      });
    }

    ////////////////////////////////////////////////////////////////////////////
    // here is the function to start the limitations 
    function range_of_links(min_range, max_range, valueofslider) {

      link.style("display", null);
      node.style("display", null);
      //  fitleration of the threshold value sidler 

      let parentnodes = node.filter(function(node) {
        if (node.type === "parentnode") {
          return node;
        }
      })

      // slider2.max = parentnodes.size();
      let filternodes3 = [];

      parentnodes.each(function(drugNode, i) {
        if (i < valueofslider) {


          d3.select(this).style("display", null);

        } else {
          filternodes3.push(drugNode.id);
          link.filter(function(linktemp) {
            if (linktemp.source === drugNode) {
              d3.select(this).style("display", "none")
            }
          })
        }
      });






      filteredLinks = link.filter(link => {
        // Filter links with a value greater than 5
        return link.value < min_range || link.value > max_range
      });
      filterNodes = node.filter(node => {

        return filteredLinks.data().some(link => (link.target === node || link.source === node));
      });
      // Hide the filtered links
      filteredLinks.style("display", "none");


      // tag2 
      //  remove the link of the selected nodes
      link.filter(function(item) {
        if (hidden_compound.includes(item.source.id)) {
          d3.select(this).style("display", "none")
        }
      });





      var filterlinks2 = link.filter(function(templink) {
        // Filter links with a value greater than 5
        let visible = d3.select(this).style("display");
        if (visible === "inline") {

          return templink;
        }
      });
      // Hide the associated target nodes
      filterNodes.style("display", "none");

      let filterNodes2 = filterNodes.filter(node => {
        // Check if there's at least one link connected to the node that is not in filteredLinks
        const isLinkedToHiddenLink = link.data().some(link => {
          return (link.target === node || link.source === node) && !filteredLinks.data().includes(link);
        });

        return isLinkedToHiddenLink;
      });
      // Reset the display style for the filtered nodes
      filterNodes2.style("display", null);

      node.each(function(d) {
        // Check if the node's MAX_PHASE is in the list_hidden
        if (list_hidden.includes(d.MAX_PHASE) && d.type === "parentnode") {
          // Node is in list_hidden, set display to "none"
          d3.select(this).style("display", "none");
        }
        if (checkbox_names.includes(d.id)) {

          d3.select(this).style("display", "none");
        }

      });
      const matchinglink = link.filter(function(link) {
        if (list_hidden.includes(link.source.MAX_PHASE) || (checkbox_names.includes(link.source.id))) {
          return link;
        }
      });
      matchinglink.style("display", "none");
      let connectedNodes;
      let allconnedtednodes = [];
      let childNode2 = node.filter(node => {
        if (node.type === "childnode" || node.type === "diseasenode") {
          return node;
        }
      })
      childNode2.style("display", "none");

      visiblenode = [];

      let maxphase = ['Approved',
        'Phase I',
        'Phase II',
        'Phase III',
        'Preclinical', 'Unknown'
      ]
      node.filter(function(node) {
        if (node.type === "parentnode") {
          let maxnode = d3.select(this).style("display");
          if (maxnode === "inline") {
            filterlinks2.filter(link => {
              if (link.source === node) {
                visiblenode.push(link.target.id);
                if (!phases.includes(node.MAX_PHASE)) {
                  if (node.MAX_PHASE === "" || node.MAX_PHASE === null) {
                    if (!phases.includes("Unknown")) {
                      phases.push("Unknown");
                    }
                  } else {
                    phases.push(node.MAX_PHASE);
                  }

                }

                let uniquedataset = ['GDSC1', 'GDSC2', 'CCLE_NP24', 'NCI-60', 'gCSI', 'FIMM'];
                if (!dataset_legend.includes(link.dataset) && uniquedataset.includes(link.dataset)) {

                  dataset_legend.push(link.dataset);
                }
                if (!matric_legend.includes(link.link_matric)) {

                  matric_legend.push(link.link_matric);
                }
                // if (!disease_Class_legend.includes(link.target.Disease_class)) {

                //   disease_Class_legend.push(link.target.Disease_class);
                // }

                if (!disease_phase_legend.includes(link.target.phase)) {

                  disease_phase_legend.push(link.target.phase);
                }

                if (!ONCOTREE_LINEAGE_legend.includes(link.target.oncotree_change)) {
                  if (link.target.oncotree_change === "") {
                    if (!ONCOTREE_LINEAGE_legend.includes("Unknown")) {
                      ONCOTREE_LINEAGE_legend.push("Unknown")

                    }
                  } else {
                    ONCOTREE_LINEAGE_legend.push(link.target.oncotree_change);
                  }
                }
              }

            })
          }
        }
      })



      legendinfo();
      if (not_remove) {}

      pax_phasecliked.on("click", onclickmax_phase);

      datasettext_click.on("click", onclick_dataSet);

      matric_click.on("click", onclick_dataSet);

      phase_click.on("click", onclick_dataSet);

      child_clicked.on("click", onclick_childnodes);

      disease_clicked.on("click", onclick_childnodes);

      phases = [];


      node.filter(function(node) {
        if (visiblenode.includes(node.id)) {
          d3.select(this).style("display", null);
        }
      })
      // ended    
      //    child nodes will be filter here


      let childnodefilteration = node.filter(function(childNode) {
        if (list_hidden_childnode.includes(childNode.oncotree_change) || checkbox_names.includes(childNode.id)) {
          return childNode;
        }
      });


      childnodefilteration.style("display", "none");
      let source_node = [];
      let matchinglinkpart = link.filter(function(link) {
        if (list_hidden_childnode.includes(link.target.oncotree_change) || checkbox_names.includes(link.target.id)) {
          source_node.push(link.source.id)
          return (
            link
          );
        }
      });


      matchinglinkpart.style("display", "none");
      node.each(function(d) {
        // d3.select(this).style("display", "none");   
        if (source_node.includes(d.id)) {
          const connectedLinks = link.filter(
            (link) => link.source.id === d.id
          );
          const data = connectedLinks.data().map((link) => link.target.oncotree_change);
          const flag2 = data.every((item) => list_hidden_childnode.includes(item));
          if (flag2) {
            d3.select(this).style("display", "none");
            var displayStyle = d3.select(this).style("display");
          }
        }
      });

      // tagCompound here see the the issue it will filter 
      let connectedchild2 = [];
      let connectedLinks2 = [];
      node.each(function(d) {
        let visible = d3.select(this).style("display");
        link.filter(function(item) {
          if (hidden_compound.includes(item.source.id)) {

            if (d.id === item.target.id && !connectedchild2.includes(d.id) && visible === "inline") {

              connectedchild2.push(d.id);
            }

          }
        })
      })


      node.each(function(d) {
        if (connectedchild2.includes(d.id)) {
          let connectedLinks2 = [];
          let connectedLinks3 = link.filter(function(templink) {
            if (templink.target.id === d.id) {
              connectedLinks2.push(templink);
              return templink;
            }

          })


          let flag5 = true;
          connectedLinks3.each(function(link) {
            let visible = d3.select(this).style("display")
            if (visible === "inline") {
              flag5 = false;
              return false;
            }

          });
          connectedLinks3 = [];

          if (flag5) {
            d3.select(this).style("display", "none");
          }

        }
      })

      node.each(function(d) {
        if (d.type === "parentnode") {
          var nodestyle = d3.select(this).style("display");
          const connectedLinks = link.filter(link => link.source.id === d.id);
          // Array to store styles of connected links
          var linkStyles = [];

          connectedLinks.each(function(link) {
            var linkStyle = d3.select(this).style("display");

            linkStyles.push(linkStyle);
          });
          // Check if every style in the array is "none"
          var allLinksNone = linkStyles.every(style => style === "none");
          if (allLinksNone && !hidden_compound.includes(d.id)) {
            // Set node style to "display: none"
            d3.select(this).style("display", "none");
          }
        }
      });

      // link filter nodes here 
      link.filter(function(templink) {
        if (list_hidden_dataset.includes(templink.dataset) || list_hidden_dataset.includes(templink.link_matric)) {

          d3.select(this).style("display", "none");
          node.filter(function(tempnode) {
            if (tempnode === templink.target || tempnode === templink.source) {
              var nodestyle = d3.select(this).style("display");

              const connectedLinks = link.filter(link => link.source.id === tempnode.id || link.target.id === tempnode.id);
              // Array to store styles of connected links
              var linkStyles = [];
              connectedLinks.each(function(link) {
                var linkStyle = d3.select(this).style("display");
                linkStyles.push(linkStyle);
              });
              // Check if every style in the array is "none"
              var allLinksNone = linkStyles.every(style => style === "none");

              if (allLinksNone) {
                // Set node style to "display: none"
                d3.select(this).style("display", "none");
              }
            }
          })
        }
      })
      //  link filter nodes ended here 
      // export csv 
      csvfile = [];
      visible_node = [];
      visible_parentnode = [];
      visible_childnode = [];

      link.filter(function(linkshow) {
        // 'this' refers to the current DOM element
        let visibility2 = d3.select(this).style("display");
        // Check if the visibility is "inline" or any other condition you need
        if (visibility2 === "inline") {
          response.filter(maindata => {
            if (linkshow.value === maindata.VALUE) {
              if (!csvfile.includes(maindata)) {

                csvfile.push(maindata);
              }

            }

          })
        }
      });
      node.filter(function(node) {
        // Select the current node using D3 and get its "display" property
        let visibility = d3.select(this).style("display");
        if (visibility === "inline") {
          visible_node.push(node.id);
          if (node.type === "parentnode") {
            visible_parentnode.push(node.id);
          }
          if (node.type === "childnode") {
            visible_childnode.push(node.id);
          }
        }

      });
      let child_count = visible_childnode.length;
      let parent_count = visible_parentnode.length;
      let child_count_D = document.getElementById("child_count")
      child_count_D.innerHTML = child_count;
      let parent_count_D = document.getElementById("parent_count")

      let parent_count_D2 = document.getElementById("parent_count2")
      parent_count_D.innerHTML = parent_count;

      parent_count_D2.innerHTML = parent_count;


      generateNameList();

      //  tag3 

      // this will not remove the compound which do not have the visible node 

      node.filter(function(node) {
        if (node.type === "parentnode" && hidden_compound.includes(node.id)) {

          d3.select(this)

            .selectAll("circle") // Select all circles within this node
            .data([node]) // Bind data to the selection
            .enter() // Enter selection
            .append("circle") // Append circle if it doesn't exist
            .attr("r", 17) // Adjust the radius as needed
            .style("fill", "none") // Adjust fill color
            .style("stroke", "black") // Adjust stroke color
            .attr("cx", 2) // Move 2 pixels to the right
            .attr("cy", -2)
            .style("stroke-dasharray", "5,5");; // Move 2 pixels up

          return true; // Keep this node in the selection
        } else if (node.type === "parentnode") {
          d3.select(this)
            .selectAll("circle")
            .remove(); // Remove circle if it exists

          return false; // Exclude this node from the selection
        }
      })

      node.each(function(d) {
        if (filternodes3.includes(d.id)) {
          d3.select(this).style("display", "none");
        }
      });


      // ended 

      // Filter out isolated nodes
    }

    // legenddata

    function clearGraph() {
      const svg = d3.select("#forcenetwork");
      svg.selectAll("*").remove();


      hidden_compound = [];
      connectedchild2 = [];
      not_remove = true;
      nodes = [];
      links = [];
      slider_range = 100;
      slider2.max = 100;
      minValue = 4;
      // minSlider.min = 6 
      var inputElement = document.querySelector('.input-min');
      // Get the value of the input element
      if (pic50) {
        inputElement.value = pic50;
        minSlider.value = pic50;
      } else {
        inputElement.value = 4;

        minSlider.value = 4;

      }

      rangeValue.textContent = 100;
    }
    /// here is the code of applying the logic of theslider_rangeclear maxphase 
    // setting the sidler valus 
    const minSlider = document.getElementById("min_slider");
    const maxSlider = document.getElementById("max_slider");
    const rangetext = document.getElementById("rangeValue");
    // Function to log the values of both sliders

    function logSliderValues() {
      not_remove = true;

      rangeValue.textContent = slider2.value;
      minValue = parseFloat(minSlider.value);
      maxValue = parseFloat(maxSlider.value);

      slider_range = parseFloat(slider2.value);

      pax_phasecliked.on("click", onclickmax_phase);

      datasettext_click.on("click", onclick_dataSet);

      matric_click.on("click", onclick_dataSet);

      phase_click.on("click", onclick_dataSet);

      child_clicked.on("click", onclick_childnodes);

      disease_clicked.on("click", onclick_childnodes);

      range_of_links(minValue, maxValue, slider_range);

    }
    // Add onchange event listeners to both sliders
    minSlider.addEventListener("change", logSliderValues);
    maxSlider.addEventListener("change", logSliderValues);
    slider2.addEventListener("change", logSliderValues);

    // slider value ended here 


    document.getElementById("submitButton").addEventListener("click", function(event) {


      // Reset error messages
      document.querySelector(".in_de_Crement").style.visibility = "visible";

      document.querySelectorAll(".alert2").forEach(function(alert) {
        alert.style.display = "none";
      });
      document.querySelectorAll(".error-border").forEach(function(element) {
        element.classList.remove("error-border");
      });

      if (Chembl_id1.length === 0 && DataPlatform.length === 0 && MaxPhase1.length === 0 && oncotree_change1.length === 0 && pic50.length === 0 && disease_class1.length === 0 && compound_class1.length === 0) {
        // Show error messages for the empty dropdowns
        event.preventDefault();


        // Get the screen width
        var screenWidth = window.innerWidth;
        // Set the threshold value for hiding elements (e.g., 100px)
        var threshold = 1000;

        // Get the elements by their IDs
        var dp1 = document.getElementById("dp1");
        var dp2 = document.getElementById("dp2");
        var dp3 = document.getElementById("dp3");
        var dp4 = document.getElementById("dp4");
        var dp5 = document.getElementById("dp5");
        // Check the screen size and hide elements if the condition is met
        if (screenWidth <= threshold) {
          alert("select the option first ");
          dp1.style.display = "none";
          dp2.style.display = "none";
          dp3.style.display = "none";
          dp4.style.display = "none";
          dp5.style.display = "none";
        } else {
          // Show elements if the screen size is greater than the threshold
          dp1.style.display = "block";
          dp2.style.display = "block";
          dp3.style.display = "block";
          dp4.style.display = "block";
          dp5.style.display = "block";
        }



      } else {


        event.preventDefault();

        document.getElementById("dp1").style.display = "none";
        document.getElementById("dp2").style.display = "none";
        document.getElementById("dp3").style.display = "none";
        document.getElementById("dp4").style.display = "none";
        document.getElementById("dp5").style.display = "none";

        ajax();
      }
    });


    document.getElementById("submitButton").click();
    // slider code 

    const rangeInput = document.querySelectorAll(".range-input input");
    const priceInput = document.querySelectorAll(".price-input input");
    const range = document.querySelector(".slider .progress");
    let priceGap = 0.1;

    priceInput.forEach(input => {
      input.addEventListener("input", (e) => {
        let minPrice = parseFloat(priceInput[0].value);
        let maxPrice = parseFloat(priceInput[1].value);

        if (maxPrice - minPrice >= priceGap && maxPrice <= parseFloat(rangeInput[1].max)) {
          if (e.target.className === "input-min") {
            rangeInput[0].value = minPrice;
            range.style.left = ((minPrice / parseFloat(rangeInput[0].max)) * 100) + "%";
          } else {
            rangeInput[1].value = maxPrice;
            range.style.right = 100 - (maxPrice / parseFloat(rangeInput[1].max)) * 100 + "%";
          }
        }
      });
    });

    rangeInput.forEach(input => {
      input.addEventListener("input", (e) => {
        let minVal = parseFloat(rangeInput[0].value);
        let maxVal = parseFloat(rangeInput[1].value);

        if (maxVal - minVal < priceGap) {
          if (e.target.className === "range-min") {
            rangeInput[0].value = maxVal - priceGap;
          } else {
            rangeInput[1].value = minVal + priceGap;
          }
        } else {
          priceInput[0].value = minVal;
          priceInput[1].value = maxVal;
          range.style.left = ((minVal / parseFloat(rangeInput[0].max)) * 100) + "%";
          range.style.right = 100 - (maxVal / parseFloat(rangeInput[1].max)) * 100 + "%";
        }
      });
    });
  </script>
  <!-- overlayascript?  -->
  <script src="js_scripts/redraw.js"></script>
  <!-- // capture picture  -->
  <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script> -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
  <script src="export_to_diffrentForm.js"></script>
</body>

</html>