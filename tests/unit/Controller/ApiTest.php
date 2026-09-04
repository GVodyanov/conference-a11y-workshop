<?php

declare(strict_types=1);

namespace Controller;

use OCA\ChaoticFileCleaner\AppInfo\Application;
use OCA\ChaoticFileCleaner\Controller\ApiController;
use OCA\ChaoticFileCleaner\Service\FileWheelService;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase {
	private FileWheelService&MockObject $service;
	private IUserSession&MockObject $userSession;
	private ApiController $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->service = $this->createMock(FileWheelService::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->controller = new ApiController(
			Application::APP_ID,
			$this->createMock(IRequest::class),
			$this->service,
			$this->userSession,
		);
	}

	private function loginAs(string $userId): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($userId);
		$this->userSession->method('getUser')->willReturn($user);
	}

	public function testWheelReturnsCandidates(): void {
		$this->loginAs('alice');
		$files = [['id' => 7, 'name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42]];

		$this->service->expects($this->once())
			->method('pickCandidates')
			->with('alice', 12)
			->willReturn(['files' => $files, 'total' => 1]);

		$data = $this->controller->wheel(12)->getData();

		$this->assertSame($files, $data['files']);
		$this->assertSame(1, $data['total']);
	}

	/**
	 * @return list<array{int, int}> Requested limit and the limit that reaches the service
	 */
	public static function limitProvider(): array {
		return [
			'below the minimum' => [0, 2],
			'negative' => [-5, 2],
			'inside the range' => [8, 8],
			'above the maximum' => [500, 40],
		];
	}

	/**
	 * @dataProvider limitProvider
	 */
	public function testWheelClampsTheLimit(int $requested, int $expected): void {
		$this->loginAs('alice');

		$this->service->expects($this->once())
			->method('pickCandidates')
			->with('alice', $expected)
			->willReturn(['files' => [], 'total' => 0]);

		$this->controller->wheel($requested);
	}

	public function testWheelRequiresALoggedInUser(): void {
		$this->userSession->method('getUser')->willReturn(null);

		$this->expectException(OCSForbiddenException::class);

		$this->controller->wheel();
	}

	public function testDestroyReturnsTheDeletedFile(): void {
		$this->loginAs('bob');
		$file = ['id' => 7, 'name' => 'cat.jpg', 'path' => 'Photos/cat.jpg', 'size' => 42];

		$this->service->expects($this->once())
			->method('delete')
			->with('bob', 7)
			->willReturn($file);

		$this->assertSame($file, $this->controller->destroy(7)->getData()['deleted']);
	}

	public function testDestroyTranslatesAMissingFile(): void {
		$this->loginAs('bob');
		$this->service->method('delete')->willThrowException(new NotFoundException('gone'));

		$this->expectException(OCSNotFoundException::class);

		$this->controller->destroy(7);
	}

	public function testDestroyTranslatesAProtectedFile(): void {
		$this->loginAs('bob');
		$this->service->method('delete')->willThrowException(new NotPermittedException('nope'));

		$this->expectException(OCSForbiddenException::class);

		$this->controller->destroy(7);
	}
}
