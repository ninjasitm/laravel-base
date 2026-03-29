<?php
// Development-only symbol shims for optional integrations. Runtime usage still requires the real packages.
namespace Laravel\Socialite\Contracts {
    if (! interface_exists(Factory::class)) {
        interface Factory {
            public function driver($driver);
        }
    }

    if (! interface_exists(User::class)) {
        interface User {
            public function getId();

            public function getEmail();
        }
    }
}

namespace DMS\PHPUnitExtensions\ArraySubset {
    if (! trait_exists(ArraySubsetAsserts::class)) {
        trait ArraySubsetAsserts {
        }
    }
}

namespace Laravel\Cashier {
    if (! class_exists(Cashier::class)) {
        class Cashier {
            public const STRIPE_VERSION = 'stub';

            public static function ignoreMigrations(): void {
            }

            public static function stripeOptions(): array {
                return [];
            }
        }
    }
}

namespace Laravel\Passport {
    if (! class_exists(Passport::class)) {
        class Passport {
            public static function tokensCan(array $abilities): void {
            }
        }
    }
}

namespace Recurr {
    if (! class_exists(Rule::class)) {
        class Rule {
            public function setStartDate($startDate, bool $inclusive = false): self {
                return $this;
            }

            public function setFreq(string $frequency): self {
                return $this;
            }

            public function setTimezone(string $timezone): self {
                return $this;
            }

            public function setByDay(array $days): self {
                return $this;
            }

            public function setInterval(int $interval): self {
                return $this;
            }

            public function setUntil($until): self {
                return $this;
            }

            public function setEndDate($endDate): self {
                return $this;
            }

            public function setCount(int $count): self {
                return $this;
            }

            public function getString(): string {
                return '';
            }
        }
    }
}

namespace Recurr\Transformer {
    if (! class_exists(RecurrenceCollection::class)) {
        class RecurrenceCollection implements \Countable, \IteratorAggregate {
            protected array $items;

            public function __construct(array $items = []) {
                $this->items = array_values($items);
            }

            public function count(): int {
                return count($this->items);
            }

            public function get($index) {
                return $this->items[$index] ?? null;
            }

            public function remove($index) {
                $item = $this->get($index);
                unset($this->items[$index]);
                $this->items = array_values($this->items);

                return $item;
            }

            public function getIterator(): \Traversable {
                return new \ArrayIterator($this->items);
            }
        }
    }

    if (! class_exists(ArrayTransformer::class)) {
        class ArrayTransformer {
            public function transform($rule): RecurrenceCollection {
                return new RecurrenceCollection();
            }
        }
    }
}

namespace Laravel\Socialite\Two {
    if (! class_exists(AbstractProvider::class)) {
        abstract class AbstractProvider {
            public function stateless() {
                return $this;
            }

            public function user() {
                return null;
            }

            public function userFromToken($token) {
                return null;
            }

            public function scopes($scopes) {
                return $this;
            }

            public function setScopes($scopes) {
                return $this;
            }
        }
    }
}

namespace MadWeb\SocialAuth\Contracts {
    if (! interface_exists(SocialAuthenticatable::class)) {
        interface SocialAuthenticatable {
            public function socials();

            public function attachSocialCustom($social, string $socialId, string $token, ?string $offlineToken = null, ?int $expiresIn = null);

            public function isAttached($social);
        }
    }
}

namespace MadWeb\SocialAuth\Controllers {
    if (! class_exists(SocialAuthController::class)) {
        class SocialAuthController extends \Illuminate\Routing\Controller {
            protected $auth;

            protected $socialite;

            protected $redirectTo;

            protected $userModel;

            protected $manager;

            protected function processData(...$arguments) {
                return null;
            }

            protected function redirectPath(): string {
                return (string) ($this->redirectTo ?? '/');
            }
        }
    }
}

namespace MadWeb\SocialAuth\Events {
    if (! class_exists(SocialUserAttached::class)) {
        class SocialUserAttached {
            public function __construct(...$arguments) {
            }
        }
    }

    if (! class_exists(SocialUserDetached::class)) {
        class SocialUserDetached {
            public function __construct(...$arguments) {
            }
        }
    }
}

namespace MadWeb\SocialAuth\Exceptions {
    if (! class_exists(SocialGetUserInfoException::class)) {
        class SocialGetUserInfoException extends \Exception {
            public function __construct($social = null, $message = '', $code = 0,  ? \Throwable $previous = null) {
                parent::__construct(is_string($message) ? $message : '', (int) $code, $previous);
            }
        }
    }

    if (! class_exists(SocialUserAttachException::class)) {
        class SocialUserAttachException extends \Exception {
            public function __construct($message = '', $social = null, $code = 0,  ? \Throwable $previous = null) {
                parent::__construct(is_string($message) ? $message : '', (int) $code, $previous);
            }
        }
    }
}

namespace MadWeb\SocialAuth\Models {
    if (! class_exists(SocialProvider::class)) {
        class SocialProvider extends \Illuminate\Database\Eloquent\Model {
        }
    }
}

namespace MadWeb\SocialAuth {
    if (! class_exists(SocialProviderManager::class)) {
        class SocialProviderManager {
            protected $social;

            public function __construct($social = null) {
                $this->social = $social;
            }

            public function socialUserQuery(string $key) {
                return new class {
                    public function exists() : bool {
                        return false;
                    }
                };
            }

            public function getUserByKey(string $key) {
                return null;
            }
        }
    }
}

namespace NotificationChannels\Fcm {
    if (! class_exists(FcmMessage::class)) {
        class FcmMessage {
            public static function create() : self {
                return new self();
            }

            public function setData($data): self {
                return $this;
            }

            public function setAndroid($config): self {
                return $this;
            }

            public function setApns($config): self {
                return $this;
            }
        }
    }
}

namespace NotificationChannels\Fcm\Resources {
    if (! class_exists(AndroidConfig::class)) {
        class AndroidConfig {
            public static function create(): self {
                return new self();
            }

            public function setPriority($priority): self {
                return $this;
            }

            public function setCollapseKey($collapseKey): self {
                return $this;
            }

            public function setFcmOptions($options): self {
                return $this;
            }
        }
    }

    if (! class_exists(AndroidFcmOptions::class)) {
        class AndroidFcmOptions {
            public static function create(): self {
                return new self();
            }

            public function setAnalyticsLabel($label): self {
                return $this;
            }
        }
    }

    if (! class_exists(AndroidMessagePriority::class)) {
        class AndroidMessagePriority {
            public static function NORMAL(): string {
                return 'normal';
            }
        }
    }

    if (! class_exists(ApnsConfig::class)) {
        class ApnsConfig {
            public static function create(): self {
                return new self();
            }

            public function setHeaders(array $headers): self {
                return $this;
            }

            public function setFcmOptions($options): self {
                return $this;
            }
        }
    }

    if (! class_exists(ApnsFcmOptions::class)) {
        class ApnsFcmOptions {
            public static function create(): self {
                return new self();
            }

            public function setAnalyticsLabel($label): self {
                return $this;
            }
        }
    }
}

namespace NotificationChannels\Fcm\Exceptions {
    if (! class_exists(CouldNotSendNotification::class)) {
        class CouldNotSendNotification extends \Exception {
        }
    }
}

namespace Nitm\Content\Models {
    if (! class_exists(ChatMessage::class)) {
        class ChatMessage extends \Nitm\Content\Models\Model {
            protected $guarded = [];
        }
    }
}

namespace Stripe {
    if (! class_exists(Coupon::class)) {
        class Coupon {
            public $valid = false;

            public static function retrieve(...$arguments): self {
                return new self();
            }
        }
    }
}

namespace {
    if (! class_exists('Google_Client')) {
        class Google_Client {
            public function setClientId($clientId): void {
            }

            public function setClientSecret($clientSecret): void {
            }

            public function setAccessType($accessType): void {
            }

            public function setApprovalPrompt($approvalPrompt): void {
            }

            public function setRedirectUri($redirectUri): void {
            }

            public function setIncludeGrantedScopes($includeGrantedScopes): void {
            }

            public function refreshToken($refreshToken): void {
            }

            public function getAccessToken(): array {
                return [];
            }

            public function fetchAccessTokenWithAuthCode($code): array {
                return [];
            }

            public function fetchAccessTokenWithRefreshToken($refreshToken): array {
                return [];
            }

            public function revokeToken($token): bool {
                return true;
            }
        }
    }
}