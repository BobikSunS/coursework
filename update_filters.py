#!/usr/bin/env python3

# Read the calculator.php file
with open('/workspace/calculator.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the filter links to preserve form data
content = content.replace(
    '?filter=all&from=<?= $from ?>&to=<?= $to ?>&package_type=<?= $_POST[\'package_type\'] ?? \'\' ?>&weight=<?= $_POST[\'weight\'] ?? ($_POST[\'package_type\'] === \'letter\' ? ($_POST[\'letter_count\'] ?? 1) * 0.02 : \'\') ?>&letter_count=<?= $_POST[\'letter_count\'] ?? \'\' ?>&gabarit=<?= $_POST[\'gabarit\'] ?? \'small\' ?>&delivery_speed=<?= $_POST[\'delivery_speed\'] ?? \'standard\' ?>&insurance=<?= isset($_POST[\'insurance\']) ? \'1\' : \'0\' ?>',
    '?filter=all&carrier=<?= $_POST[\'carrier\'] ?? $_GET[\'carrier\'] ?? \'\' ?>&from=<?= $_POST[\'from\'] ?? $_GET[\'from\'] ?? \'\' ?>&to=<?= $_POST[\'to\'] ?? $_GET[\'to\'] ?? \'\' ?>&package_type=<?= $_POST[\'package_type\'] ?? $_GET[\'package_type\'] ?? \'\' ?>&weight=<?= $_POST[\'weight\'] ?? $_GET[\'weight\'] ?? ($_POST[\'package_type\'] === \'letter\' ? ($_POST[\'letter_count\'] ?? $_GET[\'letter_count\'] ?? 1) * 0.02 : \'\') ?>&letter_count=<?= $_POST[\'letter_count\'] ?? $_GET[\'letter_count\'] ?? \'\' ?>&gabarit=<?= $_POST[\'gabarit\'] ?? $_GET[\'gabarit\'] ?? \'small\' ?>&delivery_speed=<?= $_POST[\'delivery_speed\'] ?? $_GET[\'delivery_speed\'] ?? \'standard\' ?>&insurance=<?= isset($_POST[\'insurance\']) || isset($_GET[\'insurance\']) ? \'1\' : \'0\' ?>'
)

content = content.replace(
    '?filter=cheapest&from=<?= $from ?>&to=<?= $to ?>&package_type=<?= $_POST[\'package_type\'] ?? \'\' ?>&weight=<?= $_POST[\'weight\'] ?? ($_POST[\'package_type\'] === \'letter\' ? ($_POST[\'letter_count\'] ?? 1) * 0.02 : \'\') ?>&letter_count=<?= $_POST[\'letter_count\'] ?? \'\' ?>&gabarit=<?= $_POST[\'gabarit\'] ?? \'small\' ?>&delivery_speed=<?= $_POST[\'delivery_speed\'] ?? \'standard\' ?>&insurance=<?= isset($_POST[\'insurance\']) ? \'1\' : \'0\' ?>',
    '?filter=cheapest&carrier=<?= $_POST[\'carrier\'] ?? $_GET[\'carrier\'] ?? \'\' ?>&from=<?= $_POST[\'from\'] ?? $_GET[\'from\'] ?? \'\' ?>&to=<?= $_POST[\'to\'] ?? $_GET[\'to\'] ?? \'\' ?>&package_type=<?= $_POST[\'package_type\'] ?? $_GET[\'package_type\'] ?? \'\' ?>&weight=<?= $_POST[\'weight\'] ?? $_GET[\'weight\'] ?? ($_POST[\'package_type\'] === \'letter\' ? ($_POST[\'letter_count\'] ?? $_GET[\'letter_count\'] ?? 1) * 0.02 : \'\') ?>&letter_count=<?= $_POST[\'letter_count\'] ?? $_GET[\'letter_count\'] ?? \'\' ?>&gabarit=<?= $_POST[\'gabarit\'] ?? $_GET[\'gabarit\'] ?? \'small\' ?>&delivery_speed=<?= $_POST[\'delivery_speed\'] ?? $_GET[\'delivery_speed\'] ?? \'standard\' ?>&insurance=<?= isset($_POST[\'insurance\']) || isset($_GET[\'insurance\']) ? \'1\' : \'0\' ?>'
)

content = content.replace(
    '?filter=fastest&from=<?= $from ?>&to=<?= $to ?>&package_type=<?= $_POST[\'package_type\'] ?? \'\' ?>&weight=<?= $_POST[\'weight\'] ?? ($_POST[\'package_type\'] === \'letter\' ? ($_POST[\'letter_count\'] ?? 1) * 0.02 : \'\') ?>&letter_count=<?= $_POST[\'letter_count\'] ?? \'\' ?>&gabarit=<?= $_POST[\'gabarit\'] ?? \'small\' ?>&delivery_speed=<?= $_POST[\'delivery_speed\'] ?? \'standard\' ?>&insurance=<?= isset($_POST[\'insurance\']) ? \'1\' : \'0\' ?>',
    '?filter=fastest&carrier=<?= $_POST[\'carrier\'] ?? $_GET[\'carrier\'] ?? \'\' ?>&from=<?= $_POST[\'from\'] ?? $_GET[\'from\'] ?? \'\' ?>&to=<?= $_POST[\'to\'] ?? $_GET[\'to\'] ?? \'\' ?>&package_type=<?= $_POST[\'package_type\'] ?? $_GET[\'package_type\'] ?? \'\' ?>&weight=<?= $_POST[\'weight\'] ?? $_GET[\'weight\'] ?? ($_POST[\'package_type\'] === \'letter\' ? ($_POST[\'letter_count\'] ?? $_GET[\'letter_count\'] ?? 1) * 0.02 : \'\') ?>&letter_count=<?= $_POST[\'letter_count\'] ?? $_GET[\'letter_count\'] ?? \'\' ?>&gabarit=<?= $_POST[\'gabarit\'] ?? $_GET[\'gabarit\'] ?? \'small\' ?>&delivery_speed=<?= $_POST[\'delivery_speed\'] ?? $_GET[\'delivery_speed\'] ?? \'standard\' ?>&insurance=<?= isset($_POST[\'insurance\']) || isset($_GET[\'insurance\']) ? \'1\' : \'0\' ?>'
)

# Write the updated content back to the file
with open('/workspace/calculator.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Filter links updated successfully!")