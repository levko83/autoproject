<style>
ul li {display:inline;}
</style>
<?php
// $con=mysqli_connect("localhost","admin_default","Hcur0fbAcg","admin_default");
$mysql_hostname = "localhost"; 
$mysql_user = "admin_default";            
$mysql_password = "Hcur0fbAcg";           
$mysql_database = "admin_default"; 

$bd = mysql_connect($mysql_hostname, $mysql_user, $mysql_password) or die("Opps some thing went wrong");
mysql_select_db($mysql_database, $bd) or die("Error on database connection");

function paginate($reload, $page, $tpages) {
$adjacents = 2;
$prevlabel = "&lsaquo; Prev";
$nextlabel = "Next &rsaquo;";
$out = "";
// previous
if ($page == 1) {
    $out.= "<span>".$prevlabel."</span>\n";
} elseif ($page == 2) {
    $out.="<li><a href=\"".$reload."\">".$prevlabel."</a>\n</li>";
} else {
    $out.="<li><a href=\"".$reload."&amp;page=".($page - 1)."\">".$prevlabel."</a>\n</li>";
}
$pmin=($page>$adjacents)?($page - $adjacents):1;
$pmax=($page<($tpages - $adjacents))?($page + $adjacents):$tpages;
for ($i = $pmin; $i <= $pmax; $i++) {
    if ($i == $page) {
        $out.= "<li class=\"active\"><a href=''>".$i."</a></li>\n";
    } elseif ($i == 1) {
        $out.= "<li><a href=\"".$reload."\">".$i."</a>\n</li>";
    } else {
        $out.= "<li><a href=\"".$reload. "&amp;page=".$i."\">".$i. "</a>\n</li>";
    }
}

if ($page<($tpages - $adjacents)) {
    $out.= "<a style='font-size:11px' href=\"" . $reload."&amp;page=".$tpages."\">" .$tpages."</a>\n";
}
// next
if ($page < $tpages) {
    $out.= "<li><a href=\"".$reload."&amp;page=".($page + 1)."\">".$nextlabel."</a>\n</li>";
} else {
    $out.= "<span style='font-size:11px'>".$nextlabel."</span>\n";
}
$out.= "";
return $out;
}


$per_page = 1000;         // number of results to show per page

$result = mysql_query("SELECT * FROM user_info where city='9' and b_y >1992 and b_y < 1998 and sex=0");

$total_results = mysql_num_rows($result);
$total_pages = ceil($total_results / $per_page);//total pages we going to have
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
$show_page = $_GET['page']; //current page
if ($show_page > 0 && $show_page <= $total_pages) {
    $start = ($show_page - 1) * $per_page;
    $end = $start + $per_page;
} else {
    // error - show first set of results
    $start = 0;              
    $end = $per_page;
}
} else {
// if page isn't set, show first set of results
$start = 0;
$end = $per_page;
}
// display pagination
$page = intval($_GET['page']);
$tpages=$total_pages;
if ($page <= 0)
$page = 1;
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages;
echo '<div class="pagination"><ul>';
if ($total_pages > 1) {
    echo paginate($reload, $show_page, $total_pages);
}
echo "</ul></div>";
// display data in table
echo "<table class='table table-bordered'>";
echo "<thead><tr><th>Nume</th> <th>Telefon</th></tr></thead>";
// loop through results of database query, displaying them in the table 
for ($i = $start; $i < $end; $i++) {
    // make sure that PHP doesn't try to show results that don't exist
    if ($i == $total_results) {
        break;
    }
    // echo out the contents of each row into a table
    echo "<tr " . $cls . ">";
    echo '<td>' . mysql_result($result, $i, 'name') . '</td>';
    echo '<td>' . mysql_result($result, $i, 'gsm') . '</td>';
    echo "</tr>";
}       
// close table>
echo "</table>";
// pagination