<!DOCTYPE html>
<html>

<head>
	<title>Not Found! Go Back...</title>
	<link rel="shortcut icon" href="https://www.svgrepo.com/show/206435/alert.svg" type="image/x-icon">

	<!-- ===== Google Fonts | Roboto ===== -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: "Roboto", sans-serif;
		}
		body {
			background: hsl(240 5.9% 10%);
			color: hsl(240 4.8% 95.9%);
			width: 100%;
			height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
		} 
		h1 {
			font-size: 3rem;
			margin-bottom: .5rem;
			letter-spacing: .35rem;
		}
		#btn {
			border: none;
			padding: 1rem 2rem;
			border-radius: .5rem;
			background: hsl(172.5 66% 50.4%);
			color: hsl(240 10% 3.9%);
			box-shadow: 5px 5px 0 0 hsl(161.4 93.5% 30.4%);
			cursor: pointer;
			font-size: 1.3rem;
			transition: all .3s ease-in;
		}
		#btn:hover {
			box-shadow: none;
		}
	</style>
</head>

<body>
	<h1 class="title">Not Found!</h1>
	<button id="btn">Go Back</button>

	<script>
		const domain = window.location.origin;
		const btn = document.getElementById('btn');

		btn.addEventListener('click', ()=>{
			window.location.href = domain;
		})
	</script>
</body>

</html>