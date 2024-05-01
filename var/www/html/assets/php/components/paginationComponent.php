<!-- Paganition component that should have already declared and init limit, page, and total page.  -->
<?php
	// fallback
	if(!isset($limit)){
		$limit = 20;
	}
	else{
		$limit = $limit > 100 ? 100 : $limit;
		// TODO: Log limit breached here...
	}
	if(!isset($page)){
		$page = 0;
	}
	if(!isset($totalPages)){
		$totalPages = 1;
	}
	$pageStart = $page == 0 ? "disabled" : "";
	$pageEnd = $page == $totalPages ? "disabled" : "";

	if($page < 0 || $page > $totalPages){
		// log attempt here...
		$page = 0;
	}
	$base = $baseURL.$queryParams.'&limit='.$limit.'&page=';
?>
<nav aria-label="Search results page" class="flex-container flex-row">
  <ul class="pagination">
    <?php echo '<li class="'.$pageStart.'">'; 
	 echo '<a href="'.$base.($page-1).'" aria-label="Previous">'; ?>
		  <span aria-hidden="true">&laquo;</span>
      </a>
    </li>
    <?php 
		// set current nav to middle...
		if($totalPages > 10 && $page >= 5){
			echo '<li class="disabled"><a>...</a></li>';
			for($i = $page - 5 + 1; $i < $page; $i++){
				echo '<li><a href="'.$base.$i.'">'.$i.'</a></li>';
			}
			echo '<li class="active"><a href="#" >'.$i.'</a></li>';
			// sets the second half(if any);
			$n = $totalPages > $page + 5 + 1 ? $page + 5 + 1: $totalPages;
			for($i = $page + 2; $i <= $n; $i++ ){
				echo '<li><a href="'.$base.$i.'">'.$i.'</a></li>';
			}
			if($totalPages > $page+ 5 + 1){
				echo '<li class="disabled"><a>...</a></li>';
			}
		}
		// at first 5 elements or number of totalPages <= 10
		else{
			$n = $totalPages > 10 ? 10 : $totalPages;
			for($i = 0; $i < $n; $i++){
				if ($i + 1 == $page){
					echo '<li><a href="'.$base.$i.'" class"active">'.$i.'</a></li>';
				}
				else{
					echo '<li><a href="'.$base.$i.'">'.$i.'</a></li>';
				}
			}
			if($totalPages > 10){
 				echo '<li class="disabled"><a>...</a></li>';
			}
		}
    ?>  
		<?php 
			echo '<li class="'.$pageEnd.'">';
			
				echo '<a href="'.$base.($page+1).'" aria-label="Next">';
				echo '<span aria-hidden="true">&raquo;</span>';
				echo '</a>';
			echo '</li>';
			?>
        

  </ul>
</nav>