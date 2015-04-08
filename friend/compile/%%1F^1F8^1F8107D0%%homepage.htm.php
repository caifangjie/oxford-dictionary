<?php /* Smarty version 2.6.28, created on 2014-12-24 21:06:26
         compiled from homepage.htm */ ?>
<html>
<body bgcolor='#RGB(67,65,65)'>
<div style='margin-left:200px;'>

<?php $_from = $this->_tpl_vars['image']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['picture']):
?>
<img src="<?php echo $this->_tpl_vars['picture']; ?>
" />
<?php endforeach; endif; unset($_from); ?>

<div style='margin-left:320px;'><?php echo $this->_tpl_vars['nagv']; ?>
</div>
</div>
</body>
</html>