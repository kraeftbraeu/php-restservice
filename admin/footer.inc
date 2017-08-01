<?php
	$year = date("Y");
	$remaining = date_diff(new DateTime(), new DateTime($year.'-12-25'))->format('%a');
?>
	</div>
	
	<!-- footer -->
	<footer class="footer">
	  <div class="container">
	    <p class="text-muted">
	    &copy; Krämer  2016<?php echo "-".$year; ?>
	    </p>
	  </div>
	</footer>
</body>
</html>
