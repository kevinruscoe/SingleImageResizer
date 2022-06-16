# SingleImageResizer
A Magento 2 module offering  a way to resize a single products images, or an individual image

# Installation

- Extract the files at app/code
- Run `bin/magento setup:upgrade`

# Usage

Run `bin/magento catalog:images:resize:single` with either of the options.

- `--path "filepath"` Resizes a single image
- `--product 200` Retrevies a product by the given ID and resizes all of it's images
