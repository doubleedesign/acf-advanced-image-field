/* global acf, jQuery */
jQuery(document).ready(function ($) {
	/**
	 * Initialise instance when an advanced image field is loaded in the classic editor on its own
	 */
	acf.addAction('load_field/type=image_advanced', function (field) {
		const fieldArea = field.$el[0];
		new AdvancedImageFieldEditor(fieldArea).init();
	});

	/**
	 * Initialise instance when an advanced image field is loaded
	 * inside a flexible content module called "image" when a new one is added to the page
	 */
	acf.addAction('append', function (maybeModule) {
		if (maybeModule[0]?.dataset?.layout === 'image') {
			new AdvancedImageFieldEditor(maybeModule[0]).init();
		}
	});

	/**
	 * Initialise instance when an advanced image field is loaded in the block editor
	 */
	function initImageAdvanced(field) {
		 new AdvancedImageFieldEditor(field.$el[0]).init();
	}
	if(wp.data.select('core/editor')) {
		acf.addAction('ready_field/type=image_advanced', initImageAdvanced);  // Old ACF blocks inline editor
		acf.addAction('append_field/type=image_advanced', initImageAdvanced); // Old ACF blocks inline editor
		acf.addAction('remount_field/type=image_advanced', initImageAdvanced); // ACF Blocks v3+ overlay panel
	}
});

class AdvancedImageFieldEditor {
	constructor(fieldElement) {
		this.module = fieldElement;
		this.container = this.module.querySelector('.acf-image-uploader');
		this.preview = this.module.querySelector('.image-wrap');
		this.initialised = false;
	}

	init() {
		if(this.initialised) {
			return;
		}

		// Add the focal point indicator element
		const indicator = document.createElement('div');
		indicator.className = 'focal-point-indicator';
		this.preview.appendChild(indicator);
		this.indicator = indicator;
		// Get the input fields
		this.xField = this.module.querySelector('[data-global-key="focal-point-x"] input');
		this.yField = this.module.querySelector('[data-global-key="focal-point-y"] input');
		this.offsetXfield = this.module.querySelector('[data-global-key="image-offset-x"] input');
		this.offsetYfield = this.module.querySelector('[data-global-key="image-offset-y"] input');
		this.aspectRatioField = this.module.querySelector('[data-key$="aspect_ratio"] select');
		// Set the initial values
		this.setX(this.xField.value ? parseFloat(this.xField.value) : 50);
		this.setY(this.yField.value ? parseFloat(this.yField.value) : 50);
		this.setAspectRatio(this.aspectRatioField.value || '4:3');
		this.setOffsets(
			this.offsetXfield.value ? parseFloat(this.offsetXfield.value) : 0,
			this.offsetYfield.value ? parseFloat(this.offsetYfield.value) : 0
		);

		// Add event listeners
		// Note: The offset fields are readonly so should not have input change event handlers
		this.preview.addEventListener('click', this.handlePreviewClick.bind(this));
		this.xField.addEventListener('change', (event) => this.setX(event.target.value));
		this.yField.addEventListener('change', (event) => this.setY(event.target.value));
		this.aspectRatioField.addEventListener('change', (event) => this.setAspectRatio(event.target.value));

		// Add a resize observer to handle container size changes
		// This responds to both aspect ratio setting changes and viewport resize events
		this.resizeObserver = new ResizeObserver((entries) => this.handlePreviewSizeChange(entries[0]));
		this.resizeObserver.observe(this.container);

		this.initialised = true;
	}

	setX(x) {
		if(isNaN(x)) {
			return;
		}

		this.x = x;
		this.xField.value = x;
		this.container.style.setProperty('--focal-point-x', x);
	}

	setY(y) {
		if(isNaN(y)) {
			return;
		}

		this.y = y;
		this.yField.value = y;
		this.container.style.setProperty('--focal-point-y', y);
	}

	setAspectRatio(ratio) {
		this.aspectRatio = ratio;
		this.container.style.setProperty('--aspect-ratio', ratio.replace(':', '/'));
	}

	setOffsets(x, y) {
		if(isNaN(x) || isNaN(y)) {
			return;
		}

		this.offsetXfield.value = x;
		this.offsetYfield.value = y;
		this.container.style.setProperty('--image-offset-x', `${x}%`);
		this.container.style.setProperty('--image-offset-y', `${y}%`);
	}

	handlePreviewClick(event) {
		const rect = this.preview.getBoundingClientRect();
		const x = ((event.clientX - rect.left) / rect.width) * 100;
		const y = ((event.clientY - rect.top) / rect.height) * 100;
		this.setX(Math.round(x));
		this.setY(Math.round(y));
		this.repositionImage();
	}

	handlePreviewSizeChange(containerResizeObserverEntry) {
		if (this.timeoutId) {
			clearTimeout(this.timeoutId);
		}

		this.timeoutId = setTimeout(() => {
			this.resizeImage(containerResizeObserverEntry.contentRect.height, containerResizeObserverEntry.contentRect.width);
			this.repositionImage();
		}, 200);
	}

	resizeImage(containerHeight, containerWidth) {
		/**
		 * Use the dimensions of the preview area to adjust the image size.
		 * This, combined with the CSS defined for these elements in the main admin stylesheet,
		 * enables the image to visually fit the space without actually being cropped,
		 * which enables movement of the image within the space to show focal-point-based cropping previews on-the-fly.
		 */
		const img = this.preview.querySelector('img');
		const isPortraitImage = img.naturalHeight > img.naturalWidth;
		const isPortraitContainer = containerHeight > containerWidth;

		// In a portrait container, take up available vertical space and crop horizontally
		if (isPortraitContainer) {
			img.style.minWidth = '100%';
			img.style.width = 'auto'; // prevent distortion
			img.style.height = `${containerHeight}px`;
		}
		// Square container
		else if (containerHeight === containerWidth) {
			// note: object-fit:cover makes the focal point appear in the wrong spot
			if (isPortraitImage) {
				img.style.width = `${containerWidth}px`;
				img.style.height = 'auto';
				img.style.maxHeight = 'none';
			} else {
				img.style.width = 'auto';
				img.style.height = `${containerHeight}px`;
			}
		}
		// Landscape container
		else {
			// Portrait-orientation image
			if (isPortraitImage) {
				// take up available horizontal space and crop vertically
				img.style.width = `${containerWidth}px`;
				img.style.height = 'auto';
			}
			// Landscape or square image
			else {
				// If the image is shorter than the container
				if((img.naturalHeight / img.naturalWidth) * containerWidth < containerHeight) {
					// take up available vertical space and crop horizontally
					img.style.height = `${containerHeight}px`;
					img.style.minWidth = '100%';
					img.style.width = 'auto'; // prevent distortion
				}
				// Otherwise, take up available horizontal space and crop vertically
				else {
					img.style.minHeight = '100%';
					img.style.width = '100%';
					img.style.height = 'auto'; // prevent distortion
				}
			}
		}
	}

	repositionImage() {
		// The focal point is relative to the image wrapper, which may be overflowing the container (this is intentional)
		const containerData = this.container.getBoundingClientRect();
		const indicatorData = this.indicator.getBoundingClientRect();
		const previewData = this.preview.getBoundingClientRect();

		// If the container or preview have zero width/height, bail
		// (this happens when the editing area is hidden, e.g., ACF block modal overlay opened then closed)
		if((containerData.width === 0 && containerData.height === 0) && (previewData.height === 0 && previewData.height === 0)) {
			console.debug('Advanced Image Field: Container or preview has zero width/height, skipping repositioning.');

			return;
		}

		// Get position of indicator relative to container in pixels
		const rawRelativePosition = {
			x: indicatorData.left + (indicatorData.width / 2) - containerData.left,
			y: indicatorData.top + (indicatorData.height / 2) - containerData.top
		};
		// Transform to a percentage of the container dimensions
		const relativePositionOfIndicator = {
			x: (rawRelativePosition.x / containerData.width) * 100,
			y: (rawRelativePosition.y / containerData.height) * 100
		};
		// Get current position of the preview box relative to the container
		const currentX = previewData.left - containerData.left;
		const currentY = previewData.top - containerData.top;


		// Move the preview element so that the focal point is shown as close as possible to 50/50 without showing empty space
		const {x, y} = relativePositionOfIndicator;

		// Get dimensions
		const containerWidth = containerData.width;
		const containerHeight = containerData.height;
		const previewWidth = previewData.width;
		const previewHeight = previewData.height;

		// Calculate how much we need to move the preview to center the indicator
		const targetX = 50;
		const targetY = 50;
		const moveXPixels = (targetX - x) * containerWidth / 100;
		const moveYPixels = (targetY - y) * containerHeight / 100;

		// Calculate new centered position
		const newX = currentX + moveXPixels;
		const newY = currentY + moveYPixels;

		// Calculate movement boundaries to prevent empty space
		const maxMoveRight = 0; // preview's left edge can't go past container's left edge
		const maxMoveLeft = containerWidth - previewWidth; // preview's right edge can't go past container's right edge
		const maxMoveDown = 0; // preview's top edge can't go past container's top edge
		const maxMoveUp = containerHeight - previewHeight; // preview's bottom edge can't go past container's bottom edge

		// Clamp to boundaries
		const clampedX = Math.max(maxMoveLeft, Math.min(maxMoveRight, newX));
		const clampedY = Math.max(maxMoveUp, Math.min(maxMoveDown, newY));

		// Convert to percentage relative to container size
		const clampedXPercent = Math.round((clampedX / containerWidth) * 100);
		const clampedYPercent = Math.round((clampedY / containerHeight) * 100);

		// Set the values that will update the image position in CSS
		this.setOffsets(clampedXPercent, clampedYPercent);
	}
}
