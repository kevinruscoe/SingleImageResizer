<?php

namespace KevinRuscoe\SingleImageResizer\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\MediaStorage\Service\ImageResize;
use Magento\Catalog\Model\ProductFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

class ResizeSingleImage extends Command
{
    private $imageResize;

    private $productFactory;
    
    private $appState;

    public function __construct(State $appState, ImageResize $imageResize, ProductFactory $productFactory)
    {
        parent::__construct();

        $this->appState = $appState;
        $this->imageResize = $imageResize;
        $this->productFactory = $productFactory;
    }

    protected function configure()
    {
        $this->setName('catalog:images:resize:single')
            ->setDescription('Creates a single resized product image')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Path'
            )
            ->addOption(
                'product',
                null,
                InputOption::VALUE_REQUIRED,
                'Product'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_GLOBAL);

        if ($path = $input->getOption('path')) {
            $this->resizeByPath($path, $output);
        }

        if ($product = $input->getOption('product')) {
            $this->resizeByProduct($product, $output);
        }
    }

    private function resizeByProduct($product, $output)
    {
        $product = $this->productFactory->create()->load($product);

        if ($product->getId()) {
            $images = $product->getMediaGalleryImages();

            $output->writeln(
                sprintf(
                    '<comment>Resizing %s images for `%s`.</comment>',
                    count($images),
                    $product->getName()
                )
            );

            $counter = 0;
            foreach ($images as $image) {
                $counter++;

                $output->writeln(
                    sprintf('<comment>[%s/%s] %s.</comment>', $counter, count($images), $image->getData('file'))
                );

                $this->imageResize->resizeFromImageName($image->getData('file'));

            }

            $output->writeln(
                sprintf('<info>%s images resized.</info>', $counter)
            );

        } else {
            $output->writeln('<error>Product doesn\'t exist.</error>');
        }
    }

    private function resizeByPath($path, $output)
    {
        $resized = true;

        try {
            $this->imageResize->resizeFromImageName($path);
        } catch (\Exception $e) {
            $resized = false;

            $output->writeln(
                sprintf(
                    '<error>Failed to resize %s.</error>',
                    $path
                )
            );
        }

        if ($resized) {
            $output->writeln(
                sprintf(
                    '<info>Resized %s.</info>',
                    $path
                )
            );
        }
    }
}
