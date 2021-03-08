<?php namespace App\Product;

use App\Entity\Product;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Ewll\DBBundle\Repository\Repository;
use Ewll\DBBundle\Repository\RepositoryProvider;

class ProductReviewStatActualizer
{
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function actualize(int $productId): void
    {
        /** @var ReviewRepository $reviewRepository */
        $reviewRepository = $this->repositoryProvider->get(Review::class);
        $productRepository = $this->repositoryProvider->get(Product::class);
        /** @var Product $product */
        $product = $productRepository->findById($productId, Repository::FOR_UPDATE);
        list($reviewsNum, $goodReviewsNum) = $reviewRepository->countReviews($productId);
        $product->reviewsNum = $reviewsNum;
        $product->goodReviewsNum = $goodReviewsNum;
        $product->reviewsPercent = 0 === $reviewsNum ? null : (int)($goodReviewsNum * 100 / $reviewsNum);
        $productRepository->update(
            $product,
            [Product::FIELD_REVIEWS_NUM, Product::FIELD_GOOD_REVIEWS_NUM, Product::FIELD_REVIEWS_PERCENT]
        );
    }
}
