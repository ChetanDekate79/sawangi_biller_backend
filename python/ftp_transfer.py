import logging
import datetime
import os
import shutil
from time import sleep
from ftplib import FTP

logging.basicConfig(level=logging.DEBUG)

source_folder = [
    "C:/Inetpub/vhosts/s50-62-22-142.secureserver.net/httpdocs/JNMC_3/python/tokenFiles/",
    "C:/Inetpub/vhosts/s50-62-22-142.secureserver.net/httpdocs/JNMC_2/python/tokenFiles/",
    "C:/Inetpub/vhosts/s50-62-22-142.secureserver.net/httpdocs/JNMC/python/tokenFiles/"
]

match = "data"
pattern = "Kwhmfd"

ftp_host = "148.72.208.181"
ftp_port = 21
ftp_username = "wardha_project"
ftp_password = "wardha@123"

def send_file_to_ftp(source_path, ftp_host, ftp_port, ftp_username, ftp_password):
    # Connect to the FTP server
    ftp = FTP()
    ftp.connect(ftp_host, ftp_port)
    ftp.login(ftp_username, ftp_password)

    # Get the filename from the source path
    filename = os.path.basename(source_path)

    try:
        # Check if file already exists on the FTP server
        if filename in ftp.nlst():
            print(f"File {filename} already exists on the FTP server. Skipping copy operation.")
        else:
            # Upload the file to the FTP server
            with open(source_path, "rb") as file:
                ftp.storbinary(f"STOR {filename}", file)

            print(f"Uploaded file: {filename}")

    except Exception as e:
        print(f"Error occurred while processing {filename}: {str(e)}")

    # Close the FTP connection
    ftp.quit()

while True:
    for source_folder_path in source_folder:
        for filename in os.listdir(source_folder_path):
            try:
                if pattern in filename and filename.endswith('.csv'):
                    source_path = os.path.join(source_folder_path, filename)

                    send_file_to_ftp(source_path, ftp_host, ftp_port, ftp_username, ftp_password)

                    os.remove(source_path)  # Remove the file from the source folder
                    print(f"Removed {filename} from {source_folder_path}")

            except Exception as e:
                print(f"Error occurred while processing {filename}: {str(e)}")

    sleep(10)