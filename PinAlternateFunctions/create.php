<?php

$list = glob('*.txt');
$pin_list = array();
foreach ($list as $filename) {
    //echo "$filename\n";
    $file = file_get_contents($filename);
    $lines = explode("\n", $file);
    $pin_name = "";
    $title_list = array();
    $chipname = pathinfo($filename, PATHINFO_FILENAME);
    foreach ($lines as $n => $line) {
        if (empty($line)) {
            continue;
        }
        if ($n == 0) {
            $title_list = explode("\t", $line);
            continue;
        }
        $data = explode("\t", $line);
        if ($data[0] != "") {
            $pin_name = trim($data[0], " \t\n\r\0\x0B\xc2\xa0");
        }
        foreach ($data as $i => $d) {
            if ($i == 0) {
                continue;
            }
            $d = trim($d, " \t\n\r\0\x0B\xc2\xa0/");
            if ($d == "") {
                continue;
            }

            // data patch
            if ($d == "C1NO") {
                $d = "C1N0";
            }
            if ($d == "C2NO") {
                $d = "C2N0";
            }
            if ($d == "ADC_1N1") {
                $d = "ADC_IN1";
            }
            if ($d == "0PA4_OUT0") {
                $d = "OPA4_OUT0";
            }
            if ($d == "TIM2_CH1_ET") {
                $d = "TIM2_CH1_ETR_2";
            }
            if ($d == "R_2") {
                continue;
            }

            $d = str_replace("/", " ", $d);
            $d = str_replace("  ", " ", $d);
            $d = str_replace("_ ", "_", $d);
            $d = str_replace(" _", "_", $d);
            $d = str_replace("_I N", "_IN", $d);
            $d = str_replace("_O UT", "_OUT", $d);
            $d = str_replace("_OU T", "_OUT", $d);
            $d = str_replace("_ET R", "_ETR", $d);

            if (strpos($d, " ") !== false) {
                $d = explode(" ", $d);
                foreach ($d as $dd) {
                    $pin_list[$chipname][$pin_name][trim($dd, " \t\n\r\0\x0B\xc2\xa0")] = trim($title_list[$i], " \t\n\r\0\x0B\xc2\xa0");
                }
            } else {
                $pin_list[$chipname][$pin_name][$d] = trim($title_list[$i], " \t\n\r\0\x0B\xc2\xa0");
            }
        }
    }
}

echo <<<EOT
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>CH32 RISC-V Pin Alternate Functions</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.combined.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/css/theme.default.min.css">
    <script>
        $(document).ready(function() {
            $('table').tablesorter({
                widthFixed: true,
                widgets: ['zebra', 'columns', 'filter', 'pager', 'resizable', 'stickyHeaders'],
            });

            setTimeout(function() {
              const params = new URLSearchParams(window.location.search);
              filter = [];
              if(params.get("chip")) filter[0] = params.get("chip");
              if(params.get("pin")) filter[1] = params.get("pin");
              if(params.get("functions")) filter[2] = params.get("functions");
              if(params.get("features")) filter[3] = params.get("features");
              $('table').trigger('search', [ filter ]);
              return false;
            }, 1000);

        });
        </script>
</head>

<body>
    <h1>CH32 RISC-V Pin Alternate Functions</h1>
    <button type="button">Update</button>
    <table>

EOT;

echo "      <thead><tr>";
echo '<th>Chip</th>';
echo '<th>Pin</th>';
echo '<th>Functions</th>';
echo '<th>Features</th>';
echo "</tr></thead>\n";

foreach ($pin_list as $chipname => $pins) {
    foreach ($pins as $pin_name => $pin) {
        foreach ($pin as $p => $v) {
            echo "      <tr>";
            echo '<td>' . $chipname . "</td>";
            echo '<td>' . $pin_name . "</td>";
            echo '<td>' . $p . "</td>";
            echo '<td>' . $v . "</td>";
            echo "</tr>\n";
        }
    }
}

echo <<<EOT
    </table>
  </body>
</html>
EOT;
