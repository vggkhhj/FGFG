<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <link type="text/css" rel="stylesheet" href="<?php echo printMA('MA_themePath');?>styles.css">
  <?php echo printMA('MA_head');?>
</head>
<body>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="border">
  <tr>
    <td><div id='headDiv'>&nbsp;</div></td>
  </tr>
  <tr>
    <td height="58"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="100%" id='leftHeadTd'><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="center">
                  <a href="index.php" id="logo"><?php echo printMA('MA_logo');?></a>
                </td>
              </tr>
              <tr>
                <td align="center" class="slogan">Панель администрирования</td>
              </tr>
            </table></td>
          <td width="32" height='58'><div id='rightHeadTd'>&nbsp;</div></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td height="26"><div id='headSep'>&nbsp;</div></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="background: white">
              <tr>
                <td class="mainheading"><?php echo printMA('MA_pageTitle');?></td>
              </tr>
              <tr>
                <td valign="top" height="8">&nbsp;</td>
              </tr>
              <tr>
                <td valign="top" class='contentClass'><?php echo printMA('MA_content');?></td>
              </tr>
              <tr>
                <td valign="top">&nbsp;</td>
              </tr>
            </table></td>
          <td width="180" valign="top" bgcolor='#393a3c'><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td>
                  <div class='navLinks'>
	                  <?php if ($_SESSION['user'] && in_array($_SESSION['user']['user_role'], array(1))): ?>
		                  <a href="<?php echo HREF_ADMIN?>add_to_table.php?tableName=my_admin_users&recordId=1" <?php if ($_GET['add'] && $_GET['add']=='my_admin_users') echo 'style="font-weight: bold"'?>><img width="15" height="15" border="0" title="" alt="" src="css/icons/black_n_white/6.png">&nbsp;&nbsp;Баланс сайта</a>
	                  <?php endif?>
                    <?php echo printMA('MA_navigation');?>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class='mainLinks'>
                    <?php echo printMA('MA_mainLinks');?>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class='mainLinks'>
                    <?php echo printMA('MA_siteLinks');?>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class='mainLinks'>
                    <?php echo printMA('MA_siteDataLinks');?>
                  </div>
                </td>
              </tr>
              <tr>
                <td height="600" valign="top" align="center" bgcolor="#393a3c">
                  &nbsp;
                </td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><div id='headSep'>&nbsp;</div></td>
  </tr>
</table>
</body>

</html>