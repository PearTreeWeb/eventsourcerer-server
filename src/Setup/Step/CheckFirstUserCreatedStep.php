<?php

declare(strict_types=1);

namespace App\Setup\Step;

use App\Domain\User\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

final readonly class CheckFirstUserCreatedStep implements SetupStepInterface
{
    public function __construct(private UserRepository $userRepository) {}

    public function label(): string
    {
        return 'Check first user has been created';
    }

    public function run(Request $request): SetupStepResult
    {
        try {
            $users = $this->userRepository->all();
        } catch (\Throwable $e) {
            return SetupStepResult::failure('Could not query users: ' . $e->getMessage());
        }

        if (count($users) === 0) {
            return SetupStepResult::failure(
                'No user has been created yet. Run "php bin/console app:user:register-first" to create the initial super user.'
            );
        }

        return SetupStepResult::success(sprintf('%d user(s) exist.', count($users)));
    }
}
