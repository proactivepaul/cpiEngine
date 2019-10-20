<?php

// engine.php 
// 20191019 1832
// www.criticalinfrastructureprotector.com 
// CIP Engine by ProactivePaul

// the CIP Engine interogates a weather dataset and plost the known intensity and path of
// tornados and typhoons, and by using regression analysis predicts the future path and intensity
// to do = the intensity element

// *****************************************************************************************************************************
// 0000TITLE
 
$title              = "Engine";
$thisScript         = "engine.php";
  
// *****************************************************************************************************************************
// 0011HOUSEKEEPING

require ('config.php');
require ('commonfns.php'); 


// *****************************************************************************************************************************
// 1111VARIABLES SET OR RESET 

$errMsg                       = "";
$dErrMsg                      = "";
$echoString                   = "";
$engineTable001               = "";

// FLAGS - some or all of the following flags could be combined into one activity
// they are broken down into separate activities in order to make the script
// more manageable and more human readable

$workOutDataRange             = 0; 
$collectRelevantData          = 0;
$graphTheData                 = 0;
$buildDataTable               = 0;
$showEngineTable              = 0;

//$daysUnderEnquiry = see config.php

// CANVAS

$graphCanvasDivisor           = $daysUnderEnquiry - 1;  

// *****************************************************************************************************************************
// 2222VALIDATE

if(1 == 1)
    { 
        // no data - this is the first call to this script
        
        $workOutDataRange     = 1;
        $collectRelevantData  = 1;
        $graphTheData         = 1;
        $buildDataTable       = 0;
        $showEngineTable      = 1;
    }
else
    {
        //undefined error

        $lErrCode = 17501;

        $errMsg = "Undefined error - Error code ".$lErrCode;
    }

// *****************************************************************************************************************************
// 3333DATABASE WORK

// ***********************************************************************************
// workOutDataRange 

if($workOutDataRange == 1)
    {
        //
        // *******************************************************************************
        // preamble
        
        // the DB records were not necessarily in the correct date order when they were uploaded
        // the most effective way to order them is to select all the data and array_multisort
        // however, that could be a lot of data (one year? two years?) and will (a) mean lots 
        // of processsing and (b) slow down the server, so we need a pramatic approach
        
        // the amount of data we want is determined by $daysUnderEnquiry (typically 30 days)
        // the chances are that most of our uploaded files were uploaded in date order
        // by going back for an extra 50% before $daysUnderEnquiry (typically 45 days) we can
        // be confindent that we have all the most recent data covering $daysUnderEnquiry (typically 30 days) 
        // all this data is then subjected to array_multisort, and then the oldest data (the extra 50%)
        // is discarded, this gives us a tidy set of arrays with the most recent data 
        // covering $daysUnderEnquiry (typically 30 days)
        
        // round up in case we have an odd number of days
        
        $dataCollectionLimit = ceil($daysUnderEnquiry * 1.5);
        
        // *******************************************************************************
        // stage 000
                
        // Open connection
        $mysqli = new mysqli("$hostName", "$dbUser","$dbPass","$database");      
                
        // *******************************************************************************
        // stage 001
                
        // how many days of data do we have?
        
        $tHowManyDays = 0;

        // Prepare statement        
        if($stmt = $mysqli->prepare ("     SELECT 
                                                  mDate
                                             FROM 
                                                  nasa_dataset
                                     ")
          )
           {
               //Execute it
               $stmt->execute();
                          
               // Bind results 
               $stmt->bind_result
                   (
                       $oDate
                   );
             
               // Fetch the value
               while($stmt->fetch())
                   {
                       $tHowManyDays++;
                   }
              
               // Clear memory of results
               $stmt->free_result();
              
               // Close statement
               $stmt->close();
      	   }
       
        $echoID       = 45711 ;
     
        //$echoString = $echoString."<P>$echoID tHowManyDays is $tHowManyDays ";                
        //$echoString = $echoString."<P>$echoID dataCollectionLimit is $dataCollectionLimit ";                
                
        // *******************************************************************************
        // stage 002
        
        // mySQL: OFFSET N means "skip N rows"

        // establish OFFSET and LIMIT so we don't use all the data if we don't need to
        // OFFSET is the difference between the number of dates we have and the number of dates we want
                
        if ($dataCollectionLimit > $tHowManyDays)
            {
                $dataCollectionLimit = $tHowManyDays;
            }
                
        $dateOffset   = $tHowManyDays - $dataCollectionLimit;

        $echoID       = 45712 ;
     
        //$echoString = $echoString."<P>$echoID daysUnderEnquiry is $daysUnderEnquiry ";                
        //$echoString = $echoString."<P>$echoID tHowManyDays is $tHowManyDays ";                
        //$echoString = $echoString."<P>$echoID dataCollectionLimit is $dataCollectionLimit ";                
        //$echoString = $echoString."<P>$echoID dateOffset is $dateOffset ";                
        
        // *******************************************************************************
        // stage 00N
                
        // Close connection
        $mysqli->close();                
    }
 
// ***********************************************************************************
// collectRelevantData 

if($collectRelevantData == 1)
    {
        // *******************************************************************************
        // stage 000
                
        // Open connection
        $mysqli = new mysqli("$hostName", "$dbUser","$dbPass","$database");      
                
        // *******************************************************************************
        // stage 001
                
        // get lots of data 
        
        $cDate 	                = array();
        $cLat 	                = array();
        $cLong 	                = array();
        $cMaxDeltaVelocity 	    = array();
                
        // Prepare statement        
        if($stmt = $mysqli->prepare ("     SELECT 
                                                  mDate, 
                                                  mLat, 
                                                  mLong, 
                                                  mMaxDeltaVelocity 
                                             FROM 
                                                  nasa_dataset
                                    ")
          )
              {
                  //Execute it
                  $stmt->execute();
            
                  // Bind results 
                  $stmt->bind_result
                      (
                          $oDate,
                          $oLat,
                          $oLong,
                          $oMaxDeltaVelocity
                      );
             
                  // Fetch the value
                  while($stmt->fetch())
                      {
                         $cDate[]                 = htmlspecialchars($oDate);
                         $cLat[]                  = round(htmlspecialchars($oLat),4);
                         $cLong[]                 = round(htmlspecialchars($oLong),4);
                         $cMaxDeltaVelocity[]     = round(htmlspecialchars($oMaxDeltaVelocity),4);
                      }
              
                  // Clear memory of results
                  $stmt->free_result();
              
                  // Close statement
                  $stmt->close();
      	      }
      	              
        $echoID       = 45713 ;
   
        //$arrayString = print_r($cDate, TRUE);                   $echoString = $echoString."<P>$echoID cDate is $arrayString ";            
        //$arrayString = print_r($cLat, TRUE);                    $echoString = $echoString."<P>$echoID cLat is $arrayString ";            
        //$arrayString = print_r($cLong, TRUE);                   $echoString = $echoString."<P>$echoID cLong is $arrayString ";            
        //$arrayString = print_r($cMaxDeltaVelocity, TRUE);       $echoString = $echoString."<P>$echoID cMaxDeltaVelocity is $arrayString ";            
                
                            
        // *******************************************************************************
        // stage 002
                
        // Close connection
        $mysqli->close();                
        
        // we now have the right number of days of data, and it's in the right date order

        // *******************************************************************************
        // stage 003
                
        // tidy up the arrays
        // the data may not be in date order and that's why we need to SORT
                
        // MULTISORT - CAUTION!
                
        // sort the arrays by $cDate, then $cLong etc
                
        $cDate             = convert_numeric_to_alphanumeric_keys($cDate);
        $cLat              = convert_numeric_to_alphanumeric_keys($cLat);
        $cLong             = convert_numeric_to_alphanumeric_keys($cLong);
        $cMaxDeltaVelocity = convert_numeric_to_alphanumeric_keys($cMaxDeltaVelocity);
    
        array_multisort($cDate, $cLat, $cLong, $cMaxDeltaVelocity);
    
        $cDate             = convert_alphanumeric_to_numeric_keys($cDate);
        $cLat              = convert_alphanumeric_to_numeric_keys($cLat);
        $cLong             = convert_alphanumeric_to_numeric_keys($cLong);
        $cMaxDeltaVelocity = convert_alphanumeric_to_numeric_keys($cMaxDeltaVelocity);                
                
        $echoID       = 45714 ;
   
        //$arrayString = print_r($cDate, TRUE);              $echoString = $echoString."<P>$echoID cDate is $arrayString ";            
        //$arrayString = print_r($cLat, TRUE);               $echoString = $echoString."<P>$echoID cLat is $arrayString ";            
        //$arrayString = print_r($cLong, TRUE);              $echoString = $echoString."<P>$echoID cLong is $arrayString ";            
        //$arrayString = print_r($cMaxDeltaVelocity, TRUE);  $echoString = $echoString."<P>$echoID cMaxDeltaVelocity is $arrayString ";            

        // *******************************************************************************
        // stage 004
                
        // reindex the array keys
                
        $cDate             = array_values($cDate);
        $cLat              = array_values($cLat);
        $cLong             = array_values($cLong);
        $cMaxDeltaVelocity = array_values($cMaxDeltaVelocity);                
                
        $echoID       = 45715 ;
   
        //$arrayString = print_r($cDate, TRUE);              $echoString = $echoString."<P>$echoID cDate is $arrayString ";            
        //$arrayString = print_r($cLat, TRUE);               $echoString = $echoString."<P>$echoID cLat is $arrayString ";            
        //$arrayString = print_r($cLong, TRUE);              $echoString = $echoString."<P>$echoID cLong is $arrayString ";            
        //$arrayString = print_r($cMaxDeltaVelocity, TRUE);  $echoString = $echoString."<P>$echoID cMaxDeltaVelocity is $arrayString ";            
        
        // *******************************************************************************
        // stage 005
                
        // discard old data which does not fit $daysUnderEnquiry
                
        // add relevant data to temp arrays
                
        $tDate               = array();
        $tLat                = array();
        $tLong               = array();
        $tMaxDeltaVelocity   = array();
             
        // say we have 15 dates and we want just 10
        // we need to cycle through our arrays which are 15N long
        // discard 5 elements, keep 10, discard 5, keep 10, etc
                
        // go back $daysUnderEnquiry days long and start
        // continue to $dataCollectionLimit and the you get N days of data
        // where N is the difference between $localEndPoint and $localStartPoint
        // and we know that this difference is $daysUnderEnquiry days long because we are 
        // setting it this way - the point of the if test
        // if ($localCounter >= $localStartPoint)
        // is to know where to start and to know where to end
               
        // set some local counters
                
        $localStartPoint = $dataCollectionLimit - $daysUnderEnquiry ;  
        $localEndPoint   = $dataCollectionLimit  ;                     
        $localCounter    = 0;

        $echoID     = 45716 ;
        
        //$echoString = $echoString."<P>$echoID localStartPoint is $localStartPoint";
        //$echoString = $echoString."<P>$echoID localEndPoint is $localEndPoint";
                
        $cDateElements = count($cDate);
     
        for ($cDateCycle=0; $cDateCycle<$cDateElements ; $cDateCycle++)
            {
                if ($localCounter == $localEndPoint)
                    {
                        // reset
                        $localCounter    = 0;
                    }
                            
                if ($localCounter >= $localStartPoint)
                    {
                        // grab a value
                                
                        // $b for bugfix
                                
                        $bLocalCounter          = $localCounter;
                             
                        $bDate                  = $tDate[]               = $cDate[$cDateCycle];
                        $bLat                   = $tLat[]                = $cLat[$cDateCycle];
                        $bLong                  = $tLong[]               = $cLong[$cDateCycle];
                        $bMaxDeltaVelocity      = $tMaxDeltaVelocity[]   = $cMaxDeltaVelocity[$cDateCycle];
                                
                        $echoID     = 45717 ;
        
                        //$echoString = $echoString."<P>$echoID bLocalCounter is $bLocalCounter";
                        //$echoString = $echoString." $echoID bDate is $bDate";
                        //$echoString = $echoString." $echoID bLat is $bLat";
                        //$echoString = $echoString." $echoID bLat is $bLong";
                        //$echoString = $echoString." $echoID bMaxDeltaVelocity is $bMaxDeltaVelocity";
                                
                    }
                        
                $echoID     = 45718 ;
        
                //$echoString = $echoString."<P>$echoID localCounter is $localCounter";
                //$echoString = $echoString."           localEndPoint is $localEndPoint";
                //$echoString = $echoString." $echoID cDate is $cDate[$cDateCycle]";
                //$echoString = $echoString." $echoID cLat  is $cLat[$cDateCycle]";
                //$echoString = $echoString." $echoID cLong is $cLong[$cDateCycle]";
                //$echoString = $echoString." $echoID cMaxDeltaVelocity is $cMaxDeltaVelocity[$cDateCycle]";
                //        
                $localCounter++;
            }
                    
        $echoID       = 45719 ;
   
        //$arrayString = print_r($tDate, TRUE);              $echoString = $echoString."<P>$echoID tDate is $arrayString ";            
        //$arrayString = print_r($tLat, TRUE);               $echoString = $echoString."<P>$echoID tLat is $arrayString ";            
        //$arrayString = print_r($tLong, TRUE);              $echoString = $echoString."<P>$echoID tLong is $arrayString ";            
        //$arrayString = print_r($tMaxDeltaVelocity, TRUE);  $echoString = $echoString."<P>$echoID tMaxDeltaVelocity is $arrayString ";            
                
        // *******************************************************************************
        // stage 006
        
        // assign our temp arrays to replace the earlier current arrays
                
        unset ($cDate);
        unset ($cLat);
        unset ($cLong);
        unset ($cMaxDeltaVelocity);
                
        $cDate             = $tDate;
        $cLat              = $tLat;
        $cLong             = $tLong;
        $cMaxDeltaVelocity = $tMaxDeltaVelocity;

    }
    
//******************************************************************************************************** 
// graphTheData

if ($graphTheData == 1)
    {
        // *******************************************************************************
        // stage 000
        
        // the dimentions are set in the config file
        // 20191019 2123 for convenience they are shown and commented out here
        
        // ===============================================================
        // ===============================================================
        // $graphCanvasHeight     = 400;
        // $graphCanvasWidth      = 400;
        // extentAsPercentage is expressed as an integer, 80% is simply 80
        // $extentAsPercentage    = 80; 
        // ===============================================================
        // ===============================================================
        
        // no matter what dimentions are set, the concept is that the path of the
        // tornado, and the regression line occupy only 80% of the canvas, with
        // a 10% border on every side
        
        // the production model needs to . . . 
        
        // establish where the Lat and Long start and finish
        // in order to know which 4 possible corners of the canvas were are working
        // from and working to
        
        // the test dataset requires a start at the south west with progress to the north east
        // this is one of the 4 main possibliltes, but knowing this test dataset in advance then 
        // this script accommodates only a SW to NE progression for now
        // even more evaluation of data will be required in order to scale for almost perfect 
        // horizontal and vertical trajectories 
        
        // maps need to be sourced and scaled to the canvas, then set as a background
        
        // the main tasks for now are to . . .       
        
        // scale the data for a canvas
        // invert the data for a canvas
        // convert all the modified data to JSON 
        
        // *******************************************************************************
        // stage 001
        
        // set variables
        
        // where are we starting from?
        // where are we going to?
        
        $cLongElements    = count($cLong);
        
        $rawStartX        = $cLong[0];                // maps onto  40 for now
        $rawStartY        = $cLat[0];                 // maps onto  40 for now (inverted later)

        $rawEndX          = $cLong[$cLongElements-1]; // maps onto 360 for now
        $rawEndY          = $cLat[$cLongElements-1];  // maps onto 360 for now (inverted later)
        
        $scaleFactorX     = 0;
        $scaleFactorY     = 0;
        
        $echoID     = 45721 ;
        
        //$echoString = $echoString."<P>$echoID rawStartX rawStartY is $rawStartX $rawStartY";
        //$echoString = $echoString."<P>$echoID rawEndX rawEndY is $rawEndX $rawEndY";
        
        // rawStartX rawStartY is -81.487800 25.220000
        // rawEndX   rawEndY is   -80.021600 25.810700  
        
        // simultaneous equations
        
        // -81.487800a =  40
        // -80.021600a = 360
        
        // 25.220000b  = 360
        // 25.810700b  =  40
        
        // simultaneous equations, eliminate A
        
        // $rawStartX =  40A
        // $rawEndX   = 360A
        
        $differenceInX       = $rawEndX - $rawStartX;
        $borderBufferFactorX = $graphCanvasWidth * ((100 - $extentAsPercentage)/ 2) / 100;
        $borderBufferStartX  = $borderBufferFactorX;
        $borderBufferEndX    = $graphCanvasWidth - $borderBufferFactorX;
        $scalarX             = round(abs(($borderBufferEndX - $borderBufferStartX) / $differenceInX),4);

        $differenceInY       = $rawEndY - $rawStartY;
        $borderBufferFactorY = $graphCanvasWidth * ((100 - $extentAsPercentage)/ 2) / 100;
        $borderBufferStartY  = $borderBufferFactorY;
        $borderBufferEndY    = $graphCanvasWidth - $borderBufferFactorY;
        $scalarY             = round(abs(($borderBufferEndY - $borderBufferStartY) / $differenceInY),4);
        
        // maintain the aspect ratio by using the lesser of $scalarX or $scalarY 
        
        $canvasScalar = min($scalarX, $scalarY);
        
        $echoID     = 45722 ;
        
        //$echoString = $echoString."<P>$echoID differenceInX is $differenceInX ";
        //$echoString = $echoString."<P>$echoID borderBufferFactorX is $borderBufferFactorX ";
        //$echoString = $echoString."<P>$echoID borderBufferStartX is $borderBufferStartX ";
        //$echoString = $echoString."<P>$echoID borderBufferEndX is $borderBufferEndX ";
        //$echoString = $echoString."<P>$echoID scalarX is $scalarX ";
        //$echoString = $echoString."<P>$echoID scalarY is $scalarY ";
        //$echoString = $echoString."<P>$echoID canvasScalar is $canvasScalar ";

        // *******************************************************************************
        // stage 002
        
        // scale the data to the canvas
        
        $scaledXValue     = array();
        $scaledYValue     = array();
        
        for ($cLongCycle=0; $cLongCycle<$cLongElements ; $cLongCycle++)
            {
                $differenceInX =  round($cLong[$cLongCycle] - $rawStartX,4);
                $differenceInY =  round($cLat[$cLongCycle]  - $rawStartY,4);
                
                // d denotes "display" for echoString
                
                $scaledXValue[] =  $dXValue = round($borderBufferStartX + ((abs($differenceInX)) * $canvasScalar),0);
                $scaledYValue[] =  $dYValue = round($borderBufferStartY + ((abs($differenceInY)) * $canvasScalar),0);
                
                $echoID     = 45723 ;

                //$echoString = $echoString."<P>$echoID cLong is $cLong[$cLongCycle]";
                //$echoString = $echoString."<P>$echoID differenceInX is $differenceInX";
                //$echoString = $echoString."<P>$echoID dXValue is $dXValue";
                //$echoString = $echoString."<P>$echoID cLat is $cLat[$cLongCycle]";
                //$echoString = $echoString."<P>$echoID differenceInY is $differenceInY";
                //$echoString = $echoString."<P>$echoID dYValue is $dYValue";
            }
        
        $plottableXValue  = $scaledXValue;
        
        // *******************************************************************************
        // stage 002
        
        // invert the Y values in order to fit the canvas
        
        $plottableYValue  = array();
        
        $scaledYValueElements = count($scaledYValue);
     
        for ($scaledYValueCycle=0; $scaledYValueCycle<$scaledYValueElements ; $scaledYValueCycle++)
            {
                $plottableYValue[] = $dPlottableYValue = round($graphCanvasHeight - $scaledYValue[$scaledYValueCycle],0);
                
                $echoID     = 45724 ;

                //$echoString = $echoString."<P>$echoID scaledYValue is $scaledYValue[$scaledYValueCycle]";
                //$echoString = $echoString."<P>$echoID dPlottableYValue is $dPlottableYValue";
            }
                            
        // *******************************************************************************
        // stage 003
        
        // plot coords on the canvas
        
        $canvasHTML              = array();
        
        $jsPlottableXValue       = json_encode($plottableXValue);
        $jsPlottableYValue       = json_encode($plottableYValue);
        
        $currentCanvas           = "myCanvas001";
        
        $echoID     = 45728 ;

        $echoString = $echoString."<P>$echoID jsPlottableXValue is $jsPlottableXValue";
        $echoString = $echoString."<P>$echoID jsPlottableYValue is $jsPlottableYValue";
                
                
        
        $canvasString = $canvasString."
        
        <center>
        
        47527
        
        </center>
        
        <table border = 0 cellpadding=4 cellspacing= 4>
          <tr>
            <td>
           
           45726 
           </td>
           <td colspan=3>
           
           <center> 
           <canvas id='myCanvas001' width='$graphCanvasWidth' height='$graphCanvasHeight'
           style='border:1px solid #d3d3d3;'>
           Your browser does not support the Canvas element
           </canvas>
           </center> 
   
           <script>
           
           var jsPlottableXValue     = $jsPlottableXValue;
           var jsPlottableYValue     = $jsPlottableYValue;
                                           
           var currentCanvas      = 'myCanvas001';  // JS requires this variable to be defined in single quotes - it will not work with no quotes, nor with double quotes
   
           var xValueNow          = 0;
           var yPlotNow           = 0;
           var xPlotPrior         = 0;
           var yPlotPrior         = 0;
   
           var canvas2            = document.getElementById(myCanvas001);
           var chartLine          = canvas2.getContext('2d');
   
           var graphCanvasWidth   = $graphCanvasWidth;
           var graphCanvasDivisor = $graphCanvasDivisor;
   
           for (xValueNow = 0; xValueNow < jsPlottableXValue.length; xValueNow++)
               {
                   xPlotNow = jsPlottableXValue[xValueNow];
                   yPlotNow = jsPlottableYValue[xValueNow];

                   chartLine.lineWidth = 1;
                   chartLine.beginPath();
                   chartLine.strokeStyle = 'green';
   
                   if (xPlotNow == 0)
                       {
                           // first plot
                           // do not use 0,0 but use NOW values, because we don't want a line from 0,0 
                           // to the first X,Y value
                           
                           chartLine.moveTo(xPlotNow,yPlotNow);
                           chartLine.lineTo(xPlotNow,yPlotNow);
                       }   
                   else
                       {
                           // not the first plot, normal plot
                           chartLine.moveTo(xPlotPrior,yPlotPrior);
                           chartLine.lineTo(xPlotNow,yPlotNow);
                       }   
     
                   chartLine.stroke();
           
                   // get ready for the next invocation of this loop by
                   // noting what we plotted 'last time'
                   
                   xPlotPrior = xPlotNow;
                   yPlotPrior = yPlotNow;
               }           
           
           // add trend lines to graph
           
//           var jsRegressedXValue   = $jsRegressedXValue;
  //         var jsRegressedYValue   = $jsRegressedYValue;   
    //       
      //     var jsCoordsToPlot = $jsCoordsToPlot;        
        //   
//           for (coordCycle = 0; coordCycle < jsCoordsToPlot; coordCycle = coordCycle+2)
  //             {
    //               xCoordOne = Math.round(jsRegressedXValue[coordCycle]*(graphCanvasWidth/graphCanvasDivisor));
      //             yCoordOne = jsRegressedYValue[coordCycle];
   //
     //              xCoordTwo = Math.round(jsRegressedXValue[coordCycle+1]*(graphCanvasWidth/graphCanvasDivisor));
       //            yCoordTwo = jsRegressedYValue[coordCycle+1];
   //
     //              chartLine.strokeStyle = '#cc0000';
  //
    //               chartLine.beginPath(); 
      //             chartLine.moveTo(xCoordOne,yCoordOne);
        //           chartLine.lineTo(xCoordTwo,yCoordTwo); 
          //         chartLine.stroke();
   //
     //          }           

               
           // add deciles

           chartLine.lineWidth = 0.33;
           chartLine.strokeStyle = 'gray';

           chartLine.beginPath(); chartLine.moveTo(0,75); chartLine.lineTo(500,75); chartLine.stroke();
           chartLine.beginPath(); chartLine.moveTo(0,150); chartLine.lineTo(500,150); chartLine.stroke();
           chartLine.beginPath(); chartLine.moveTo(0,225); chartLine.lineTo(500,225); chartLine.stroke();

           // add labels

           chartLine.font = '10px Arial'

           chartLine.textBaseline = 'top';    chartLine.fillText('120%', 5, 2);
           chartLine.textBaseline = 'top';    chartLine.fillText('110%', 5, 77);
           chartLine.textBaseline = 'top';    chartLine.fillText('100%', 5, 152);
           chartLine.textBaseline = 'top';    chartLine.fillText('90%',  5, 227);
           chartLine.textBaseline = 'bottom'; chartLine.fillText('80%',  5, 298);
           
           // end of canvas
           
           </script>
           
           </td>
         </tr>
         
       </table>
        
        
        ";
    }


//******************************************************************************************************** 
// showEngineTable

if($showEngineTable == 1)
  {  
     // ***********************************************************************************************************
     // engineTable001    

    $engineTable001 = "";

    $engineTable001 = $engineTable001."

    <center>

    <h2>
    EngineTable
    </h2>
    
    $canvasString
    
    </center>

    <P>&nbsp;

    ";
  }
elseif($showEngineTable == 0)
  {
    $engineTable001 = "";
  }

// ***********************************************************************************************************
// errMsg

if ($errMsg !="") 
    {
        $dErrMsg = "<P><center><font color='red'> $errMsg </font></center>";
    }
   
// ****************************************************************************************************************
// 5555HTML IS ECHOED

require ('header.php');

echo "

$echoString

$dErrMsg

$engineTable001

 
";
 
require ('footer.php');

?>