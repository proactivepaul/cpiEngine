<?php

// commonfns.php
// 20191019 1939
// www.criticalinfrastructureprotector.com 
// CIP Engine by ProactivePaul

// ****************************************************************************************************************
// basic_regression

function basic_regression($inputValue)
{
  //******************************************************************************** 
  // unpack $regressionData
  
  $regressionData = $inputValue;
  
  //******************************************************************************** 
  // regression analysis 

  // There are many elements to these equations

  // Slope
  // a = nSIGMA(xy) - SIGMAxSIGMAy 
  //     -------------------------
  //      nSIGMAx^2 - (SIGMAx)^2 

  // offset
  // B = SIGMAy - aSIGMAx
  //     ----------------
  //            n

  // Trendline Formula
  // y = ax + B

  //**************************************************************
  // calc step 001 SLOPE

  // summation of all of x times y - SIGMA(xy)
  
  // X is the element number in our array 
  // (we use ELEMENT NUMBER PLUS ONE because we are doing arithmetic and we do not want to start at ZERO)
  // Y is the value contained in that element

    $numberOfInstances = $regressionDataElements = count($regressionData);
   
    $summationAllxTimesy = 0;
   
    for($regressionDataCycle=0; $regressionDataCycle<$regressionDataElements; $regressionDataCycle++)
      {
         $summationAllxTimesy = $summationAllxTimesy + (($regressionDataCycle + 1) * $regressionData[$regressionDataCycle]);
      }
      
    //*******************************************************
    // calc step 002 

    // multiply step 001 by n - nSIGMA(xy)

    $nSIGMAxy = $numberOfInstances * $summationAllxTimesy;
      
    //*******************************************************
    // calc step 003

    // summation all of x - SIGMAx

    $summationAllx = 0;

    for($regressionDataCycle=0; $regressionDataCycle<$regressionDataElements; $regressionDataCycle++)
      {
         $summationAllx += ($regressionDataCycle + 1);
      }
      
    //*******************************************************
    // calc step 004

    // summation all of y - SIGMAy

    $summationAlly = 0;

    for($regressionDataCycle=0; $regressionDataCycle<$regressionDataElements; $regressionDataCycle++)
      {
         $summationAlly += $regressionData[$regressionDataCycle];
      }
      
    //*******************************************************
    // calc step 005

    // step 003 answer times step 004 answer - SIGMAx * SIGMAy

    $productOfSigmaxy = $summationAllx * $summationAlly;

    //*******************************************************
    // calc step 006

    //step 002 subtract step 005 gives us numerator - nSIGMA(xy) - SIGMAxSIGMAy 

    $slopeNumerator = $nSIGMAxy - $productOfSigmaxy; 

    //*******************************************************
    // calc step 007

    // calculate each x^2 and sum it all - SIGMAx^2

    $summationAllxSquared = 0;

    for($regressionDataCycle=0; $regressionDataCycle<$regressionDataElements; $regressionDataCycle++)
      {
         $summationAllxSquared += (($regressionDataCycle + 1) * ( $regressionDataCycle + 1));
      }

    //*******************************************************
    // calc step 008

    // multiply step 007 by n - nSIGMAx^2

    $summationAllxSquaredTimesn = $numberOfInstances * $summationAllxSquared;

    //*******************************************************
    // calc step 009

    // Add up all of the x values - SIGMAx

    // summationAllx already done

    //*******************************************************
    // calc step 010

    // square the value of step 009 - (SIGMAx)^2

    $summationAllxSquared = $summationAllx * $summationAllx;

    //*******************************************************
    // calc step 011

    // do step 008 minus step 010 to get the denominator - nSIGMAx^2 - (SIGMAx)^2

    $slopeDenominator = $summationAllxSquaredTimesn - $summationAllxSquared;

    //*******************************************************
    // calc step 012

    // do step 006 divided by step 011      step 006
    //                                      --------
    //                                      step 011

    $alpha = round($slopeNumerator / $slopeDenominator,6);
    
    //*******************************************************************
    // calc step 013 OFFSET

    // Sumation all of y - SIGMAy

    //summationAlly already done

    //*******************************************************
    // calc step 014

    // Sumation all of x - SIGMAx

    //summationAllx already done

    //*******************************************************
    // calc step 015

    // multiply step 014 by the final answer of SLOPE (step 012) - aSIGMAx

    $offsetNumerator = $summationAlly - ($alpha * $summationAllx);

    //*******************************************************
    // calc step 016

    // divide the answer of step015 by n      SIGMAy - aSIGMAx
    //                                        ----------------
    //                                               n

    $beta = $offsetNumerator / $numberOfInstances;
    
    //******************************************************************
    // calc step 017 TRENDLINE FORMULA
    
    // y = ax + B

    // find the first and last y coordinates for the trendline

    $startY = round(($alpha *        1          ) + $beta,3);

    $endY   = round(($alpha * $numberOfInstances) + $beta,3);

    // 
  
    $outputValue = array($startY, $endY, $alpha);
  
    return $outputValue;
  
}

// *****************************************************************************************************************************
//convert_alphanumeric_to_numeric_keys

// 20150925 0715

function convert_alphanumeric_to_numeric_keys($inputValue)
{
    // convert_numeric_to_alphanumeric_keys and convert_alphanumeric_to_numeric_keys must be used as a pair
    
    // there is no need to use this for arrays which already have alphanumeric keys
    // restore alphanumeric keys to numeric keys
    // basically remove the "a" which we put in earlier by removing all alpha characters
     
    $outputValue = array();
        
    foreach ($inputValue as $key => $value) 
       {
           $numericKey = preg_replace("/[^0-9]/", '', $key);
   	           
           $outputValue[$numericKey] = $value;
       }            
       
    return $outputValue;

}

// *****************************************************************************************************************************
//convert_numeric_to_alphanumeric_keys

// 20150925 0715

function convert_numeric_to_alphanumeric_keys($inputValue)
{
    // convert_numeric_to_alphanumeric_keys and convert_alphanumeric_to_numeric_keys must be used as a pair
    
    // there is no need to use this for arrays which already have alphanumeric keys
    // make numeric keys into alphanumeric keys 
    // basically prefix an "a" now, then do array_multisort in the calling script, then do convert_alphanumeric_to_numeric_keys
     
    $outputValue = array();
        
    foreach ($inputValue as $key => $value) 
       {
           $alphaNumericKey = "a".$key;
   	           
           $outputValue[$alphaNumericKey] = $value;
       }            
       
    return $outputValue;

}

// ****************************************************************************************************************

?>