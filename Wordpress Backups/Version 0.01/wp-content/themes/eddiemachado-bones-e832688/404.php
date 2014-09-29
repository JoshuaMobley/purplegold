<!-- Link to 404 Stylesheet -->

	<link rel="stylesheet" type="text/css" href="404style.css" />

<!-- 404 Page Content -->

	<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

					<div id="main" class="m-all t-2of3 d-5of7 cf" role="main">

						<article id="post-not-found" class="hentry cf">

							<header class="article-header">

								<h1><?php _e( 'There is No Sanctuary! 404!', 'bonestheme' ); ?></h1>

							</header>

							<section class="entry-content">

								<p><?php _e( "The article or content you were looking for was not found, or it doesn't exist. Either way either you or I messed up along the way.", 'bonestheme' ); ?></p>

							</section>

						</article>

					</div>

				</div>

			</div>

<?php get_footer(); ?>

