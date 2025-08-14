<!DOCTYPE html>
<html lang="en" x-data="dictionary">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
	<title>Kotava Dictionary</title>
</head>
<body :class="darkMode ? 'bg-dark text-light' : ''">
	<div class="container">

		<div class="d-flex justify-content-center mt-3">
			<button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
				style="width: 48px; height: 48px; padding: 0;"
				@click="darkMode = !darkMode"
				:class="darkMode ? 'btn-light' : 'btn-dark'">
				<span x-show="!darkMode" aria-label="Switch to dark mode">&#x1F319;</span>
				<span x-show="darkMode" aria-label="Switch to light mode">&#x2600;</span>
			</button>
		</div>

		<div class="row mb-3 pt-3 align-items-center">
			<div class="col-12 col-md-6 mb-3 mb-md-0">
				<label for="searchKotava" class="form-label"><strong>Search Kotava:</strong></label>
				<input type="text" id="searchKotava" class="form-control" x-model="searchKotava" placeholder="Search Kotava..." @keyup.debounce.300ms="fetchEntries('kotava')" :class="darkMode ? 'bg-dark text-light border-secondary' : ''" autofocus>
			</div>
			<div class="col-12 col-md-6">
				<label for="searchEnglish" class="form-label"><strong>Search English:</strong></label>
				<input type="text" id="searchEnglish" class="form-control" x-model="searchEnglish" placeholder="Search English..." @keyup.debounce.300ms="fetchEntries('english')" :class="darkMode ? 'bg-dark text-light border-secondary' : ''">
			</div>
		</div>

		<div class="row mb-3 pt-0">
			<div class="col-12">
				<div class="form-check d-flex justify-content-center">
					<input class="form-check-input" type="checkbox" id="hideLegacy" x-model="hideLegacy" @click.debounce="fetchEntries()" checked>
					<label class="form-check-label ms-2" for="hideLegacy">
						Hide legacy results
					</label>
				</div>
			</div>
		</div>

		<div class="pt-0 pb-3 small text-center">
			<em>Note:</em> Less than 3 characters will search for exact matches.
		</div>

		<table class="table" :class="darkMode ? 'table-dark' : ''">
			<thead>
				<tr>
					<th scope="col">Kotava</th>
					<th scope="col">English</th>
					<th scope="col">Grammar</th>
					<th scope="col">Current status</th>
				</tr>
			</thead>
			<tbody>
				<template x-for="entry in entries" :key="entry.id">
					<tr>
						<td x-text="entry.kotava"></td>
						<td x-text="entry.english"></td>
						<td x-text="entry.grammar"></td>
						<td>
							<span x-show="!entry.status_update || entry.status_update === ''" class="badge rounded-pill bg-success">original</span>
							<span x-show="entry.status_update === 'legacy'" class="badge rounded-pill bg-danger">legacy</span>
							<span x-show="entry.status_update === 'google translated'" class="badge rounded-pill bg-warning text-dark">google translated</span>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

	</div>

	<script>
		document.addEventListener('alpine:init', () => {
			Alpine.data('dictionary', () => ({
				searchEnglish: '',
				searchKotava: '',
				lastSearched: '',
				entries: [],
				darkMode: true,
				hideLegacy: true,
				get filteredEnglish() {
					return this.entries.filter(entry =>
						entry.english.toLowerCase().includes(this.searchEnglish.toLowerCase())
					);
				},
				get filteredKotava() {
					return this.entries.filter(entry =>
						entry.kotava.toLowerCase().includes(this.searchKotava.toLowerCase())
					);
				},
				fetchEntries(language = '') {
					if (language === '' && this.lastSearched === '') {
						return;
					} 

					if (language !== '') {
						this.lastSearched = language;
					}

					if (language === '') {
						language = this.lastSearched;
					}

					const params = new URLSearchParams();

					if (language === 'english') {
						params.append('searchEnglish', this.searchEnglish);
					} else {
						params.append('searchKotava', this.searchKotava);
					}

					params.append('hideLegacy', this.hideLegacy);

					fetch(`dictionary_endpoint.php?${params.toString()}`)
						.then(res => res.json())
						.then(data => { this.entries = data; });
				}
			}));
		});
	</script>
</body>
</html>