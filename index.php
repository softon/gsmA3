<?php
/*
 * **************************************************************
 ****************  MCS EXPT3 ******************************
 ***************************************************************/
 /* Designed & Developed by
 /*                                    - Shiburaj Pappu
 /* ************  S.P.I.T *************************************
 /* ********** M.E (EXTC) Sem-II *****************************
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
 <link type="text/css" href="style.css" rel="stylesheet" />
 <script type="text/javascript" src="jquery.js"></script>
 <script type="text/javascript">
        jQuery(document).ready(function() {
            
            //////////////////////////////////////////////////////////////
            ////////////////////  Making Sim /////////////////////////////
            //////////////////////////////////////////////////////////////
            $('#mkSIM').removeAttr('disabled');
            $('#msisdn').removeAttr('disabled');
            $('#register').attr('disabled','disabled');
            $('#authenticate').attr('disabled','disabled');
            $('#mearea').attr('value','');
            $('#mscarea').attr('value','');
            
            $('#direcImg').css('display','none');
            $('#mkSIM').click(function(){
                var simMSISDN = $('#msisdn').attr('value');
               $.post('functions.php?action=makesim',{'MSISDN':simMSISDN},function(data){
                    $('#simvars').css('display','block');
                    $('#simMSISDN span').html(data.MSISDN);
                    $('#simIMSI span').html(data.IMSI);
                    $('#simIMEI span').html(data.IMEI);
                    $('#simKival').attr('value',data.Ki);
                    $('#simNSP span').html(data.NSP);
                    $('#mkSIM').attr('disabled','disabled');
                    $('#register').removeAttr('disabled');
                    $('#msisdn').attr('disabled','disabled');
                },"json"); 
            });
            
            ///////////////////////////////////////////////////////////////
            ///////////////////   Registering on Netwrok   ////////////////
            ///////////////////////////////////////////////////////////////
            $('#register').click(function(){
                var temp = $('#mearea').attr('value');
                $('#register').attr('disabled','disabled');
                $('#mearea').attr('value',temp + '>> Registering on Network...');
                $('#direcImg').css('display','block');
                var temp = $('#mearea').attr('value');
                $('#mearea').attr('value',temp + '\n>> Sending IMSI & MSISDN ...');
                var simMSISDN = $('#simMSISDN span').html();
                var simIMSI = $('#simIMSI span').html();
                var temp = $('#mscarea').attr('value');
                $('#mscarea').attr('value',temp + '>> Recieving Registeration Request ...');
                $('#regvars').css('display','block');
                $('#recdMSISDN span').html(simMSISDN);
                $('#recdIMSI span').html(simIMSI);
                $.post('functions.php?action=register',{'MSISDN':simMSISDN,'IMSI':simIMSI},function(data){
                    $('#direcImg').attr('src','left.png');
                    var temp = $('#mscarea').attr('value');
                    $('#mscarea').attr('value',temp + '\n>> Registration Successfull ...');
                    var temp = $('#mscarea').attr('value');
                    $('#mscarea').attr('value',temp + '\n>> Sending TMSI number...');
                    $('#simTMSI span').html(data.regdata.TMSI);
                    
                    $('#genTMSI span').html(data.regdata.TMSI);
                    var temp = $('#mearea').attr('value');
                    $('#mearea').attr('value',temp + '\n>> Recieved TMSI Number,Registration Successfull ...');
                    $('#authenticate').removeAttr('disabled');
                },"json"); 
            });
            
            
            ///////////////////////////////////////////////////////////////
            ///////////////////   Authenticating on Netwrok   ////////////////
            ///////////////////////////////////////////////////////////////
            $('#authenticate').click(function(){
                var temp = $('#mearea').attr('value');
                $('#authenticate').attr('disabled','disabled');
                $('#mearea').attr('value',temp + '\n>> Requesting Authentication of Device...');
                $('#direcImg').attr('src','right.png');
                var temp = $('#mearea').attr('value');
                $('#mearea').attr('value',temp + '\n>> Sending TMSI...');
                var simTMSI = $('#simTMSI span').html();
                var simMSISDN = $('#simMSISDN span').html();
                var temp = $('#mscarea').attr('value');
                $('#mscarea').attr('value',temp + '\n>> Recieving Authentication Request ...');
                $('#regvars').css('display','none');
                $('#authvars').css('display','block');
                $('#recdTMSI span').html(simTMSI);
                $.post('functions.php?action=authenticate',{'MSISDN':simMSISDN,'TMSI':simTMSI},function(data){
                    $('#direcImg').attr('src','left.png');
                    var temp = $('#mscarea').attr('value');
                    $('#mscarea').attr('value',temp + '\n>> Generated RAND,RES & Kc...');
                    var temp = $('#mscarea').attr('value');
                    $('#mscarea').attr('value',temp + '\n>> Sending RAND sequence...');
                    $('#genRAND span').html(data.RAND);
                    $('#simRAND span').html(data.RAND);
                    $('#genRES span').html(data.RES);
                    var temp = $('#mearea').attr('value');
                    $('#mearea').attr('value',temp + '\n>> Recieved RAND sequence Successfully ...');
                    
                    
                    var simRAND = $('#genRAND span').html();
                    var simKi = $('#simKival').attr('value');
                    var temp = $('#mearea').attr('value');
                    $('#mearea').attr('value',temp + '\n>> Generating SRES(Signed Response)...');
                    
                    // generate SRES
                    $.post('functions.php?action=gensres',{'RAND':simRAND,'Ki':simKi},function(data){
                        
                        var temp = $('#mearea').attr('value');
                        $('#mearea').attr('value',temp + '\n>> SRES Generated Successfully...');
                        $('#simSRES span').html(data.SRES);
                        var temp = $('#mearea').attr('value');
                        $('#mearea').attr('value',temp + '\n>> Sending SRES to MSC ...');
                        
                        //// Sending SRES
                        mscRES = $('#genRES span').html();
                        $('#recdSRES span').html(data.SRES);
                        $('#direcImg').attr('src','right.png');
                        $.post('functions.php?action=sendsres',{'SRES':data.SRES,'RES':mscRES},function(data){
                            $('#authImg').css('display','block');
                            if(data.status == 'success'){
                                var temp = $('#mearea').attr('value');
                                $('#mearea').attr('value',temp + '\n>> '+data.reason);
                                $('#authImg img').attr('src','unlocked.png');
                                var temp = $('#mscarea').attr('value');
                                $('#mscarea').attr('value',temp + '\n>> '+data.reason);
                            }else{
                                var temp = $('#mearea').attr('value');
                                $('#mearea').attr('value',temp + '\n>> '+data.reason);
                                $('#authImg img').attr('src','locked.png');
                                var temp = $('#mscarea').attr('value');
                                $('#mscarea').attr('value',temp + '\n>> '+data.reason);
                            }
                            
                            
                        },"json");
                    },"json");
                    
                    $('#authenticate').removeAttr('disabled');
                },"json"); 
                
                
            });
        });
 
 </script>
<title>MCS-Expt3 | Authentication in GSM Network</title>
</head>
    <body>
        <div style="width: 100%;border:1px solid blue;background:#D3DCE3;height: 20px;">
            <div style="width: 50%;float: left;text-align: center;">MCS-Expt. No. 3 | Authentication in GSM Network using A3 Algorithm</div>
            <div style="width: 50%;float: right;text-align: right;">Shiburaj Pappu&nbsp;&nbsp;&nbsp;</div>
        </div>
    	<div class="col1">
    		<div class="box">
                <div><img src="phone.png" width="80" height="80" /></div>
                <div><h4>MS</h4></div>
                <div><textarea id="mearea" class="codearea" readonly="readonly">hi</textarea></div><br />
                <div>Mobile Number: 
                    <input type="text" name="msisdn" id="msisdn" /><br /><br />
                    <input type="button" id="mkSIM" class="btn" value="Make SIM" /> &nbsp;
                    <input type="button" id="register" class="btn" value="Register" /> &nbsp;
                    <input type="button" id="authenticate" class="btn" value="Authenticate" />
                </div>
                <div id="simvars">
                    <div id="simNSP">SIM NSP : <span>--</span></div>
                    <div id="simMSISDN">SIM MSISDN : <span>--</span></div>
                    <div id="simIMSI">SIM IMSI : <span>--</span></div>
                    <div id="simIMEI">SIM IMEI : <span>--</span></div>
                    <div id="simKi">SIM Ki : <input type="text" name="simKival" value="" id="simKival" /></div>
                    <div id="simTMSI">SIM TMSI : <span>--</span></div>
                    <div id="simRAND">Recd. RAND : <span>--</span></div>
                    <div id="simSRES">Gen. SRES : <span>--</span></div>
                </div>
            </div>
    	</div>
    	<div class="col2">
    		<div><img id="direcImg" src="right.png" width="80" height="80" /></div>
    	</div>
    	<div class="col3">
    		<div class="box">
                <div><img src="msc.png" width="80" height="80" /></div>
                <div><h4>MSC</h4></div>
                <div><textarea id="mscarea" class="codearea" readonly="readonly"></textarea></div>
                <div id="regvars">

                    <div id="recdMSISDN">Recd. MSISDN : <span>--</span></div>
                    <div id="recdIMSI">Recd. IMSI : <span>--</span></div>
                    <div id="genTMSI">Gen. TMSI : <span>--</span></div>
                </div>
                <div id="authvars">
                    <div id="recdTMSI">Recd. TMSI : <span>--</span></div>
                    <div id="genRAND">Gen. RAND : <span>--</span></div>
                    <div id="genRES">Gen. RES : <span>--</span></div>
                    <div id="recdSRES">Recd. SRES : <span>--</span></div>
                </div>
                <div id="authImg"><img src="locked.png" width="80" height="80" /></div>
            </div>
    	</div>
    </body>
</html>