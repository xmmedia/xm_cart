<header>
	<div class="top_nav_wrapper">
		<nav>
			<ul class="left">
				<li class="home"><?php echo HTML::anchor('', __('Home')); ?></li>
				<?php if ($logged_in) { ?>
				<?php //if (Auth::instance()->allowed('cart_admin')) { ?>
				<li class="cart_admin has_subnav"><?php echo HTML::anchor(Route::get('cart_admin')->uri(), __('Shopping Cart') . '<span class="more"></span>'); ?>
					<ul class="sub_nav">
						<?php /*<li class="cart_admin_index"><?php echo HTML::anchor(Route::get('cart_admin')->uri(), __('Main')); ?></li>*/ ?>
						<li class="cart_admin_order"><?php echo HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'order')), __('Orders')); ?></li>
						<?php if (Kohana::$config->load('xm_cart.enable_shipping')) { ?>
						<li class="cart_admin_shipping"><?php echo HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping')), __('Shipping Rates')); ?></li>
						<?php } // if ?>
						<?php if (Kohana::$config->load('xm_cart.enable_tax')) { ?>
						<li class="cart_admin_tax"><?php echo HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'tax')), __('Taxes')); ?></li>
						<?php } // if ?>
					</ul>
				</li>
				<?php //} // if cart admin perm ?>

				<?php if (Auth::instance()->allowed('content_admin') || Auth::instance()->allowed('user_admin/index') || Auth::instance()->allowed('xm_db_admin') || (XM::is_dev() && Auth::instance()->allowed('userguide')) || (XM::is_dev() && Auth::instance()->allowed('xm_db_admin/model_create')) || Auth::instance()->allowed('db_change/index')) { ?>
				<li class="admin has_subnav"><?php echo HTML::anchor(Route::get('xm_db_admin')->uri(), __('Admin') . '<span class="more"></span>'); ?>
					<ul class="sub_nav">
						<?php if (Auth::instance()->allowed('user_admin/index')) { ?>
						<li class="user_admin"><?php echo HTML::anchor(Route::get('user_admin')->uri(), __('User Admin')); ?></li>
						<?php } ?>
						<?php if (Auth::instance()->allowed('user_admin/group/index')) { ?>
						<li class="user_admin_groups"><?php echo HTML::anchor(Route::get('user_admin')->uri(array('action' => 'groups')), __('Groups / Permissions')); ?></li>
						<?php } ?>
						<?php if (Auth::instance()->allowed('content_admin')) { ?>
						<li class="content_admin"><?php echo HTML::anchor(Route::get('content_admin')->uri(), __('Content Admin')); ?></li>
						<?php } ?>
						<?php if (Auth::instance()->allowed('xm_db_admin')) { ?>
						<li class="xm_db_admin"><?php echo HTML::anchor(Route::get('xm_db_admin')->uri(), __('DB Admin')); ?></li>
						<?php } ?>
						<?php if (XM::is_dev() && Auth::instance()->allowed('userguide')) { ?>
						<li class="user_guide"><?php echo HTML::anchor(Route::get('docs/guide')->uri(), __('Kohana User Guide')); ?></li>
						<li class="user_guide_api"><?php echo HTML::anchor(Route::get('docs/api')->uri(), __('Kohana API Browser')); ?></li>
						<?php } ?>
						<?php if (XM::is_dev() && Auth::instance()->allowed('xm_db_admin/model_create')) { ?>
						<li class="model_create"><?php echo HTML::anchor(Route::get('model_create')->uri(), __('Model Create')); ?></li>
						<?php } ?>
						<?php if (Auth::instance()->allowed('db_change/index')) { ?>
						<li class="db_change"><?php echo HTML::anchor(Route::get('db_change')->uri(), __('DB Change')); ?></li>
						<?php } ?>
					</ul>
				</li>
				<?php } // if db admin perm ?>
			</ul>
			<ul class="right">
				<li class="right user has_subnav"><a href="" title="My Account"><?php echo HTML::chars($user->first_name . ' ' . $user->last_name); ?><span class="more"></span></a>
					<ul class="sub_nav right">
						<?php if (Auth::instance()->allowed('account/profile')) { ?>
						<li class="account"><?php echo HTML::anchor(Route::get('account')->uri(array('action' => 'profile')), HTML::chars(__('My Account'))); ?></li>
						<?php } ?>
						<li class="logout"><?php echo HTML::anchor(Route::get('login')->uri(array('action' => 'logout')), HTML::chars(__('Logout'))); ?></li>
					</ul>
				</li>
				<?php } else { // if logged in ?>
			</ul>
			<ul class="right">
				<li class="login"><?php echo HTML::anchor(Route::get('login')->uri(), HTML::chars(__('Login'))); ?></li>
				<?php } // if logged in ?>
			</ul>
		</nav>
	</div>

	<div class="page_top">
		<div class="page_top_logo"><a href="<?php echo URL::base(); ?>"><?php echo HTML::chars(SHORT_NAME . ' v' . APP_VERSION); if (isset($pageTitle) && trim($pageTitle) != '') echo ' - ' . HTML::chars($pageTitle); ?></a></div>
	</div>
</header>