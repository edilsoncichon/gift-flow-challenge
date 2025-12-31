<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RedeemGiftCardAction;
use App\Exceptions\DomainException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\RedeemRequest;
use Symfony\Component\HttpFoundation\Response;

class RedeemGiftCardController extends Controller
{
    public function __invoke(RedeemGiftCardAction $action, RedeemRequest $request)
    {
        try {
            $response = $action->execute($request);
        } catch (NotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (DomainException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        }

        return response()->json($response, Response::HTTP_OK);
    }
}
