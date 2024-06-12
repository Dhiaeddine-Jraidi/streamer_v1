import re
import sys


def clean_vtt(input_file):
    def contains_arabic(text):
        arabic_pattern = re.compile(r'[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]+')
        return bool(arabic_pattern.search(text))

    with open(input_file, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    cleaned_lines = [lines[0]]  # Keep the first line unchanged
    current_block = []
    for line in lines[2:]:
        if '-->' in line:
            if any(contains_arabic(text) for text in current_block):
                cleaned_lines.extend(current_block)
            current_block = []
        current_block.append(line)

    if any(contains_arabic(text) for text in current_block):
        cleaned_lines.extend(current_block)

    with open(input_file, 'w', encoding='utf-8') as f:
        f.writelines(cleaned_lines)


if __name__ == "__main__":
    input_file = sys.argv[1]
    clean_vtt(input_file)