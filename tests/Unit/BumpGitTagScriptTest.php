<?php

use PHPUnit\Framework\TestCase;

class BumpGitTagScriptTest extends TestCase {
    public function testBumpGitTagSupportsOverrideReleaseNotesViaMessageFile(): void {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('--message-file)', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="$(cat "$1")"', $contents);
        $this->assertStringContainsString('--message-file requires a readable file path', $contents);
    }

    public function testBumpGitTagSupportsOverrideReleaseNotesViaNamedFlag(): void {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('case "$1" in', $contents);
        $this->assertStringContainsString('--message)', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="${1:-$MASTER_COMMIT_MESSAGE}"', $contents);
    }

    public function testBumpGitTagUsesMasterCommitMessageForReleaseNotes(): void {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringContainsString('MASTER_COMMIT_MESSAGE=`git log --format=%B -n 1 master`', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="$MASTER_COMMIT_MESSAGE"', $contents);
        $this->assertStringContainsString('RELEASE_NOTES="$MASTER_COMMIT_MESSAGE"', $contents);
        $this->assertStringContainsString('git tag -a "$NEW_TAG" -m "$RELEASE_NOTES" --force', $contents);
    }

    public function testBumpGitTagDoesNotCreateLightweightTagBeforeAnnotatedTag(): void {
        $contents = $this->fileContents('bump-git-tag.sh');

        $this->assertStringNotContainsString('git tag $NEW_TAG --force', $contents);
    }

    private function fileContents(string $relativePath): string {
        $path = dirname(__DIR__, 2) . '/' . $relativePath;

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents, 'Failed to read ' . $relativePath);

        return $contents;
    }
}