const loadUserData = () => {
	const fieldsToTable = [
		'id',
		'name',
		'username',
		'email',
		'website',
		'role',
	];

	$.get({
		url: 'resources/users.json',
	}).done((data) => {
		const $tbody = $('tbody#load-data-here')

		// remove all children
		$tbody.find('>*').remove();

		// let's build the table rows
		for (let user of data) {
			// initialize
			let $row = $('<tr>');
			$row.attr('data-id', user.id);

			// add most of the cells
			fieldsToTable.forEach((column) => {
				let $cell = $('<td>');
				$cell.text(user[column] || '');
				$row.append($cell);
			});

			// add the action buttons in a separate cell
			$row.append(generateActionsCell(user));

			// append to tbody
			$tbody.append($row);
		}
	}).fail((data, textStatus, errorThrown) => {
		alert("Hiba történt: " + errorThrown);
	})
}

/**
 * Generate table cell with action buttons.
 * @param {object} user Plain user object from AJAX request.
 * @returns {jQuery} The <td> element having a button with the event handlers.
 */
const generateActionsCell = (user) => {
	$cell = $('<td>');

	$button = $('<button>')
		.attr('type', 'button')
		.addClass('btn btn-primary')
		.text('🔍 Részletek');

	$cell.append($button);

	// Bootstrap Modal
	const bsModal = createBootstrapModal(user);

	$button.on('click', () => {
		bsModal.show();
	});

	return $cell;
}

/**
 * Generate a Bootstrap Modal object prefilled with the user attributes.
 * @param {object} user The user object having the information to show.
 * @returns A Bootstrap Modal object to show & hide.
 */
const createBootstrapModal = (user) => {
	const modalHtml = `<div class="modal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Modal title</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="field-row">
						<div class="field-title">Address:</div>
						<div class="modal-address"></div>
					</div>
					<div class="field-row">
						<div class="field-title">Company:</div>
						<div class="modal-company"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary close-button" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>`;

	const $modal = $(modalHtml);

	// initialize modal HTML content
	$modal.find('.modal-title').text(`${user.name} details`);
	$modal.find('.modal-address').html(UserAddressToHtml(user.address));
	$modal.find('.modal-company').html(UserCompanyToHtml(user.company));

	const bsModal = new bootstrap.Modal($modal.get(0));

	$modal.find('.close-button').on('click', () => bsModal.hide());

	return bsModal;
}

/**
 * Generate HTML for the user address.
 * @param {object} address The address member of the user object.
 * @returns {string} HTML code of the address.
 */
const UserAddressToHtml = (address) => {
	$html = $('<span>')
		.append($('<span>').text(address.street))
		.append($('<br>'));

	if (address.suite) {
		$html
			.append($('<span>').text(address.suite))
			.append($('<br>'));
	}

	$html
		.append($('<span>').text(address.zipcode))
		.append($('<span>').text(address.city));

	return $html.html();
}

/**
 * Generate HTML for the user company.
 * @param {object} company The company member of the user object.
 * @returns {string} HTML code of the company.
 */
 const UserCompanyToHtml = (company) => {
	$html = $('<span>')
		.append($('<span>').text(company.name))
		.append($('<br>'))
		.append($('<span>').text(company.catchPhrase))
		.append($('<br>'))
		.append($('<span>').text(company.bs));

	return $html.html();
}

// document.onload handlers
$(() => {
	// make some delay
	setTimeout(loadUserData, 1000);
});
