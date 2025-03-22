import os

def combine_code(directory, output_file="all_code.txt"):
    # List of file extensions to include (you can modify this)
    allowed_extensions = {".html", ".css", ".js", ".py", ".php", ".sql", ".json", ".xml", ".txt"}

    with open(output_file, "w", encoding="utf-8") as output:
        for root, _, files in os.walk(directory):
            for file in files:
                if any(file.endswith(ext) for ext in allowed_extensions):
                    file_path = os.path.join(root, file)
                    try:
                        with open(file_path, "r", encoding="utf-8") as f:
                            output.write(f"===== FILE: {file_path} =====\n")
                            output.write(f.read() + "\n\n")
                    except Exception as e:
                        print(f"Skipping {file_path}: {e}")

    print(f"All code has been written to {output_file}")

# Run the function (change 'your_project_folder' to the actual path)
if __name__ == "__main__":
    project_folder = input("Enter the path to your project folder: ").strip()
    combine_code(project_folder)
