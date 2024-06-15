import sys
import pandas as pd

def parse_vtt_file(file_path):
    timeframes = []
    dialogues = []

    with open(file_path, 'r', encoding='utf-8') as file:
        lines = file.readlines()

    i = 0
    while i < len(lines):
        line = lines[i].strip()
        if ' --> ' in line:
            timeframe = line
            i += 1
            dialogue = []
            while i < len(lines) and lines[i].strip() != '':
                dialogue.append(lines[i].strip())
                i += 1
            timeframes.append(timeframe)
            dialogues.append(' '.join(dialogue))
        else:
            i += 1

    df = pd.DataFrame({
        'timeframe': timeframes,
        'dialogue': dialogues
    })

    return df



if __name__ == "__main__":
    file_path = sys.argv[1]
    df = parse_vtt_file(file_path)
    df = df[df['dialogue'].str.contains(r'[\u0600-\u06FF]', regex=True)]
    df = df[["timeframe","dialogue"]]
    vtt_content = "WEBVTT\n\n"
    for index, row in df.iterrows():
        timeframe = row['timeframe']
        dialogue = row['dialogue']
        vtt_content += f"{timeframe}\n{dialogue}\n\n"

    with open(file_path, 'w', encoding='utf-8') as file:
        file.write(vtt_content)
