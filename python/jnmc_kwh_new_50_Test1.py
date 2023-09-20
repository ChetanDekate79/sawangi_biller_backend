import logging
import datetime
import time
import csv
import mysql.connector
import os
import pandas as pd
from pandas import DataFrame as dataFrame
from time import sleep
from collections import OrderedDict
from pytz import timezone
# from sqlalchemy import create_engine
# import mysql.connector
# from urllib.parse import quote_plus
# from sqlalchemy import create_engine, Column, DateTime, String, Integer
# # from sqlalchemy.ext.declarative import declarative_base
# from sqlalchemy.orm import declarative_base
# from sqlalchemy.orm import sessionmaker

# Base = declarative_base()

# class JnmcTestKwh(Base):
#     __tablename__ = 'jnmc_all_kwh'
#     dt_time = Column(DateTime, primary_key=True)
#     hour = Column(Integer)
#     host = Column(String(255), primary_key=True)
#     device_id = Column(String(255), primary_key=True)
#     wh_R = Column(Integer)
#     wh_D = Column(Integer)
#     wh_1 = Column(Integer)
#     wh_2 = Column(Integer)
#     wh_3 = Column(Integer)


class Dont_Repeat_Yourself():
    def __init__(self):
        self.tokenfile_directory='C:/inetpub/vhosts/hetadatain.in/wardha_rawcsv/python/tokenFiles/'
        self.backupDirectory='C:/inetpub/vhosts/hetadatain.in/wardha_rawcsv/python/backupFiles/'
        self.errorDirectory='C:/inetpub/vhosts/hetadatain.in/wardha_rawcsv/python/errorFiles/'
        # self.tokenfile_directory='E:/hetadatain/demojnmcall/tokenFiles/'
        # self.backupDirectory='E:/hetadatain/demojnmcall/backupFiles1/'
        # self.errorDirectory='E:/hetadatain/demojnmcall/errorFiles/'
        # self.tokenfile_directory='C:/Users/KALETHA/Desktop/HETA_DATAIN_COMPANY_FILES/13072023ExtractionStart/tokenFiles1/'
        # self.backupDirectory='C:/Users/KALETHA/Desktop/HETA_DATAIN_COMPANY_FILES/13072023ExtractionStart/backupFiles1/'
        # self.errorDirectory='C:/Users/KALETHA/Desktop/HETA_DATAIN_COMPANY_FILES/13072023ExtractionStart/errorFiles/'
        
        x =self.extractCsvFiles()
        #print("hi")


    def extractCsvFiles(self):
        extractedDates = []
        try:
            dataFileNames = self.getFilesWithExtension(self.tokenfile_directory, "Kwhmfd", ".csv")
            print("################getFilesWithExtension################\n",dataFileNames)
            #exit()

            if dataFileNames:
                dataFileNames = self.sortFilesByDateNClient(dataFileNames)
                #print("################sortFilesByDateNClient################\n",dataFileNames)
                #print("\n 1111 \n")
                dataFileNames = self.mergeFilesByDateNClient(dataFileNames)
                #print("\n 222 \n")
                #print("################mergeFilesByDateNClient################\n",dataFileNames)
            
            #exit()
            # connection,flag1=self.establish_db_connection('127.0.0.1',3307,'rcplasto','root','alti123')
            # connection,flag1=self.establish_db_connection('localhost',3306,'new_rcplasto','root','root@123')
            
            connection,flag1=self.establish_db_connection('148.72.208.181',3306,'new_rcplasto','new_rcplasto','new_rcplasto@123')
            #connection,flag1=self.establish_db_connection('50.62.22.142',3306,'sawangi_project','sawangi','sawangi@123')
            
            #print(flag1)
            if flag1:
                #print("OOOOO")
                #print("Db connection ok")
                #exit()
                logging.info("DB Connection OK")
                # for date in dataFileNames:
                #     # print (dataFileNames[date])
                #     for client in dataFileNames[date]:
                #         clientName = (client.split("."))[0]
                #         #print(clientName)
                #         inputFileName = self.tokenfile_directory + \
                #             ""+dataFileNames[date][client]
                #         # print('inputFileName\n', inputFileName)
                #         file_obj = os.stat(inputFileName)

                #         if file_obj.st_size > 0:
                #             backupFileName = self.backupDirectory + clientName + \
                #                 "/" + date + "_" + clientName + ".csv"
                #             errorFileName = self.errorDirectory + clientName + \
                #                 "/" + date + "_" + clientName + ".csv"
                #             #print("1111")
                        
                #             flag,df=self.store_merged_files_in_db(connection, inputFileName)
                #             #print("22")
                #             print("hiii",flag)
                            #exit()
                for date, client_file_map in dataFileNames.items():
                    #print("OOOOO")
                    for client, filename in client_file_map.items():
                        #print(client,filename)
                        #print("OOOOO")
                        file_path = filename[0]
                        file_obj = os.stat(file_path)
                        #print(file_path)
                        #exit()        
                        if file_obj.st_size > 0:
                            #print("OOOOO")
                            #print(file_obj.st_size)
                            backupFileName = self.backupDirectory + client + \
                                "/" + date + "_" + client + ".csv"
                            #print("OOOOO",backupFileName)
                            #exit()
                            errorFileName = self.errorDirectory + client + \
                                "/" + date + "_" + client + ".csv"
                            #print("1111")
                        
                            flag,df=self.store_merged_files_in_db(connection, file_path)
                            #print("22")
                            print("Data stored successfully",flag)

                            # if flag:
                            #     if not os.path.isdir(self.backupDirectory + client):
                            #         os.makedirs(self.backupDirectory + client)
                            #     response = self.moveToBackupFolder(
                            #         df, inputFileName, backupFileName)
                            #     extractedDates.append(date)
                            # else:
                            #     if not os.path.isdir(self.errorDirectory + client):
                            #         os.makedirs(self.errorDirectory + client)
                            #     response = self.moveToBackupFolder(
                            #         df, inputFileName, errorFileName)
                        #         #exit()
                        
                        # else:
                        #     logging.warning(inputFileName+" :file is empty")
                            if flag:
                                if not os.path.isdir(self.backupDirectory + client):
                                    os.makedirs(self.backupDirectory + client)
                                response = self.moveToBackupFolder(
                                    df, file_path, backupFileName)
                                extractedDates.append(date)
                            else:
                                if not os.path.isdir(self.errorDirectory + client):
                                    os.makedirs(self.errorDirectory + client)
                                response = self.moveToBackupFolder(
                                    df, file_path, errorFileName)
                                #exit()
                        
                        else:
                            logging.warning(file_path+" :file is empty")
            else:
                logging.warning(" DB Connection error")
            return extractedDates
        except Exception as e:
            logging.critical("EXTRACTION: "+str(e))
            print("EXTRACTION: ZZZZZ" + str(e))
        
            return extractedDates


    def df_to_csv_back(self,df, outputFileName):
        try:
            df.to_csv(outputFileName, mode='a',header=False, index = False)
            return {'status': True, 'message': 'DataFrame saved to CSV successfully.'}
        except Exception as e:
            return {'status': False, 'message': 'Error saving DataFrame to CSV: ' + str(e)}
        
    def moveToBackupFolder(self,df, inputFileName, outputFileName):
        try:
            print("@@@@@@",inputFileName)
            print("######",outputFileName)
            #exit()
            response = self.df_to_csv_back(df, outputFileName)
            if response['status']:
                os.remove(inputFileName)
                logging.info('File moved from: ' + inputFileName + ' to: ' + outputFileName)
                return True
            else:
                logging.warning('Failed to move file from: ' + inputFileName + ' to: ' + outputFileName)
                return False
        except Exception as e:
            logging.critical('Error while moving file: ' + str(e))
            return False
        
    def getFilesWithExtension(self,directory, keyword, extension):
        csv_files = []
        for filename in os.listdir(directory):
            if filename.endswith(extension) and keyword in filename:
                csv_files.append(filename)
        return csv_files


    def sortFilesByDateNClient(self,dataFileNames):
        sortedFiles = {}
        for filename in dataFileNames:
            parts = filename.split("_")
            if not (len(parts)> 3):
                date = parts[0]
                counter = parts[1]
                clientName= parts[2].split(".")[0]
            else:
                date = parts[0]
                counter = parts[1]
                clientName= parts[3].split(".")[0]

            if date not in sortedFiles:
                sortedFiles[date] = {}

            if clientName not in sortedFiles[date]:
                sortedFiles[date][clientName] = []

            sortedFiles[date][clientName].append(filename)

        # Sort files within each date and client name group
        for date in sortedFiles:
            for clientName in sortedFiles[date]:
                sortedFiles[date][clientName].sort(key=lambda x: int(x.split("_")[1].split(".")[0]))

        return sortedFiles
    
    def mergeFilesByDateNClient(self,sortedFiles):
    
        mergedFiles = {}
        #print("QQQQQQQ")

        for date in sortedFiles:
            for clientName in sortedFiles[date]:
                mergedData = []
                #print(mergedData)
                # Read and merge data from all files with the same date and client name
                for i, filename in enumerate(sortedFiles[date][clientName]):
                    filePath = self.tokenfile_directory + filename
                    #print("aaaa",filePath)
                    with open(filePath, 'r') as file:
                        reader = csv.reader(file)
                        data = list(reader)

                    # Skip the header row if not the first file
                    if i > 0:
                        data = data[1:]
                        #print("sss",data)
                    mergedData.extend(data)

                # Overwrite the data in the first file with the merged data
                
                targetFilePath = self.tokenfile_directory + sortedFiles[date][clientName][0]
                #print("tttt",targetFilePath)
                with open(targetFilePath, 'w', newline='') as file:
                    writer = csv.writer(file)
                    writer.writerows(mergedData)
                #mergedFiles[date][clientName]=[]
                if date not in mergedFiles:
                    mergedFiles[date] = {}
                if clientName not in mergedFiles[date]:
                    mergedFiles[date][clientName] = []
                mergedFiles[date][clientName].append(targetFilePath)
                
            #mergedFiles[date] = {clientName:targetFilePath}

        return mergedFiles

    # def mergeFilesByDateNClient(self, dataFileNames):
    #     try:
    #         for date in dataFileNames:
    #             for client in dataFileNames[date]:
    #                 sourceDir = self.tokenfile_directory
    #                 destFullFilePath =self. tokenfile_directory + \
    #                     dataFileNames[date][client][0]
    #                 self.mergeFiles(
    #                     dataFileNames[date][client], sourceDir, destFullFilePath)
    #                 dataFileNames[date][client]=dataFileNames[date][client][0]
    #                 #logging.info("Data File Names after merging: %s", dataFileNames)
    #                 #for temp in filesarray:
    #                     #dataFileNames[date][client] = temp
    #                 #print('datafiles:\n', dataFileNames)
    #         return dataFileNames
    #     except Exception as e:
    #         logging.critical(e)
      
    # def mergeFiles(self, filesArray, sourceDir, destFullFilePath):
    #     try:
    #         for tempFile in filesArray:
    #             tempFile = sourceDir + tempFile
    #             #print('tempFile:\n', tempFile)
    #             if(tempFile != destFullFilePath):
    #                 if (self.checkIfFileExists(destFullFilePath)):
    #                     fout = open(destFullFilePath, 'a')
    #                     fin = open(tempFile)
    #                     for line in fin:
    #                         fout.write(line)
    #                     fin.close()
    #                     os.remove(tempFile)
    #                 else:
    #                     print("No. Creating new")
    #     except Exception as e:
    #         logging.critical(e)

    # def checkIfFileExists(self, fileName):
    #     if(os.path.isfile(fileName)):
    #         return 1
    #     else:
    #         return 0
      
    
    def establish_db_connection(self,host, port, database, user, password):
        try:
            connection = mysql.connector.connect(
                host=host,
                port=port,
                database=database,
                user=user,
                password=password,
            )
            print("Database connection established successfully.")
            return connection,True
        except mysql.connector.Error as error:
            print("Error while connecting to the database:", error)
            return None,False



# edited code from AI
    # def store_merged_files_in_db(self, connection, merged_files):
    # try:
    #     cursor = connection.cursor()

    #     df = pd.read_csv(merged_files, header=None)
    #     df = df.fillna(value=0)

    #     if df.shape[1] == 9:
    #         df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D', 'wh_1', 'wh_2', 'wh_3']

    #         if pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S', errors='coerce').notnull().all():
    #             df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #         else:
    #             df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')

    #         processed_combinations = set()

    #         for row in df.itertuples(index=False):
    #             dt_date = row.dt_time.split(' ')[0]
    #             host = row.host
    #             device_id = row.device_id
    #             combination = (dt_date, host, device_id)

    #             dt_time_date_hour = row.dt_time[:13]
    #             dt_time_with = dt_time_date_hour + "%"

    #             select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
    #             cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
    #             result = cursor.fetchone()

    #             if result is not None:
    #                 delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s"
    #                 cursor.execute(delete_query, (dt_time_date_hour + "%", row[1], row[2], row[3]))

    #                 insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                 cursor.execute(insert_query, row)

    #                 if combination not in processed_combinations:
    #                     processed_combinations.add(combination)

    #                     if row[0].split(' ')[1][:3] == '00:':
    #                         new_row = list(row)
    #                         new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                         new_row[1] = 24

    #                         insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                         cursor.execute(insert_query1, new_row)

    #             elif result is None:
    #                 insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                 cursor.execute(insert_query, row)

    #         connection.commit()
    #         return True, df

    #     else:
    #         return False, df

    # except Exception as e:
    #     return False, df
    
    def store_merged_files_in_db(self, connection, merged_files):
        try:        
            cursor = connection.cursor()
            
           
            # Read the CSV file into a DataFrame
            df = pd.read_csv(merged_files, header=None)
            # Replace nan values with None
            df = df.fillna(value=0)
            # print(df)
            # exit()
            # Set the column names of the DataFrame to match the column names of the database table
            if df.shape[1] == 9:
                df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D', 'wh_1', 'wh_2', 'wh_3']
                 # Convert the dates in the dt_time column to the correct format
                #df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
                #df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
                format1 = '%Y-%m-%d %H:%M:%S'
                format2 = '%Y-%m-%d %H:%M'
                format3 = '%d-%m-%Y %H:%M:%S'
                format4 = '%d-%m-%Y %H:%M'

                # Convert to datetime using format1
                dt_time = pd.to_datetime(df['dt_time'], format=format1, errors='coerce')
                if dt_time.notnull().all():
                    df['dt_time'] = dt_time.dt.strftime(format1)
                else:
                # Convert to datetime using format2
                    dt_time = pd.to_datetime(df['dt_time'], format=format2, errors='coerce')
                if dt_time.notnull().all():
                    df['dt_time'] = dt_time.dt.strftime(format1)
                else:
                # Convert to datetime using format3
                    dt_time = pd.to_datetime(df['dt_time'], format=format3, errors='coerce')
                if dt_time.notnull().all():
                    df['dt_time'] = dt_time.dt.strftime(format1)
                else:
                # Convert to datetime using format4
                    dt_time = pd.to_datetime(df['dt_time'], format=format4, errors='coerce')
                if dt_time.notnull().all():
                    df['dt_time'] = dt_time.dt.strftime(format1)
                else:
                # Handle remaining invalid datetime values
                    df['dt_time'] = pd.NaT
               
                # Insert or update rows in the jnmc_all_kwh table
                print('#####  9 wala ',df)
                 # Keep track of the combinations of date, host, and device_id that have been processed
                processed_combinations = set()
                
                
                # Insert or update rows in the jnmc_all_kwh table
                for row in df.itertuples(index=False):
                      # Extract the date, host, and device_id from the row
                    #try:  
                        dt_date = row.dt_time.split(' ')[0]
                    
                        host = row.host
                        device_id = row.device_id
                        combination = (dt_date, host, device_id)
                    
                    
                    # Extract the date and hour from row.dt_time
                        dt_time_date_hour = row.dt_time[:13]
                        dt_time_with=dt_time_date_hour+"%"
                        print(dt_time_with)
                    # Check if a row with the same dt_time (with the specified date and hour), host, and device_id values already exists in the table
                    # existing_row = session.query(JnmcTestKwh).filter(
                        select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
                        cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
                        result = cursor.fetchone()
                    # print(result)
                    # print(len(result))
                    # exit()
                    
                        if result is not None:
                        # print(result)
                        # exit()
                        
                            # Update the existing row with the new values from the DataFrame
                        # Insert the original row into the database
                            delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s" 
                            cursor.execute(delete_query, (dt_time_date_hour+"%", row[1], row[2], row[3]))

                            insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
                            cursor.execute(insert_query,row)
                            print("###### IF MAIN  #####")
                         # Check if this is the first entry for the combination of date, host, and device_id
                            if combination not in processed_combinations:
                            # Add the combination to the set of processed combinations
                                processed_combinations.add(combination)

                            # Check if the timestamp is '00:__:__'
                                if row[0].split(' ')[1][:3] == '00:':
                                # Create a new row with the modified timestamp
                                    new_row = list(row)
                                    new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
                                    new_row[1]=24

                                # Insert the new row into the database
                                    insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
                                #insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
                                    cursor.execute(insert_query1,new_row)
                                    print("convert 00:00 to 23:59 as 24",new_row[0],new_row[1],row[3])
                        # connection.commit()
                        elif result is None:
                        # for row in df.itertuples(index=False):
                            insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
                            cursor.execute(insert_query, row)
                            print('####  ELIF ELIF #####')
                            if combination not in processed_combinations:
                            # Add the combination to the set of processed combinations
                                processed_combinations.add(combination)

                            # Check if the timestamp is '00:__:__'
                                if row[0].split(' ')[1][:3] == '00:':
                                # Create a new row with the modified timestamp
                                    new_row = list(row)
                                    new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
                                    new_row[1]=24

                                # Insert the new row into the database
                                    insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
                                #insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
                                    cursor.execute(insert_query1,new_row)
                                    print("convert 00:00 to 23:59 as 24",new_row[0],new_row[1],row[3])
                                # print("###### ELSE IF ########")
                        connection.commit()
            # if df.shape[1] == 6:
            #     df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D']
            #      # Convert the dates in the dt_time column to the correct format
            #     # df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
            #     #df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
            #     format1 = '%Y-%m-%d %H:%M:%S'
            #     format2 = '%Y-%m-%d %H:%M'
            #     format3 = '%d-%m-%Y %H:%M:%S'
            #     format4 = '%d-%m-%Y %H:%M'

            #     # Convert to datetime using format1
            #     dt_time = pd.to_datetime(df['dt_time'], format=format1, errors='coerce')
            #     if dt_time.notnull().all():
            #         df['dt_time'] = dt_time.dt.strftime(format1)
            #     else:
            #     # Convert to datetime using format2
            #         dt_time = pd.to_datetime(df['dt_time'], format=format2, errors='coerce')
            #     if dt_time.notnull().all():
            #         df['dt_time'] = dt_time.dt.strftime(format1)
            #     else:
            #     # Convert to datetime using format3
            #         dt_time = pd.to_datetime(df['dt_time'], format=format3, errors='coerce')
            #     if dt_time.notnull().all():
            #         df['dt_time'] = dt_time.dt.strftime(format1)
            #     else:
            #     # Convert to datetime using format4
            #         dt_time = pd.to_datetime(df['dt_time'], format=format4, errors='coerce')
            #     if dt_time.notnull().all():
            #         df['dt_time'] = dt_time.dt.strftime(format1)
            #     else:
            #     # Handle remaining invalid datetime values
            #         df['dt_time'] = pd.NaT
                    
            #     # Insert or update rows in the jnmc_all_kwh table
            #     print(" 6 wala ",df)
            #     # exit() 
                
                
            #     # Keep track of the combinations of date, host, and device_id that have been processed
            #     processed_combinations = set()
                
                
            #     # Insert or update rows in the jnmc_all_kwh table
            #     for row in df.itertuples(index=False):
            #           # Extract the date, host, and device_id from the row
            #         dt_date = row.dt_time.split(' ')[0]
            #         # exit()
            #         host = row.host
            #         device_id = row.device_id
            #         # exit()
            #         #  Create a tuple of the date, host, and device_id
            #         combination = (dt_date, host, device_id)

            #         # Extract the date and hour from row.dt_time
            #         dt_time_date_hour = row.dt_time[:13]
            #         dt_time_with=dt_time_date_hour+"%"
            #         print(dt_time_with)
            #         # Check if a row with the same dt_time (with the specified date and hour), host, and device_id values already exists in the table
            #         # existing_row = session.query(JnmcTestKwh).filter(
            #         select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
            #         cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
            #         result = cursor.fetchone()
                    
            #         if result is not None:
                        
            #             delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s" 
            #             cursor.execute(delete_query, (dt_time_date_hour+"%", row[1], row[2], row[3]))

            #             insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
            #             cursor.execute(insert_query,row)
            #             print("###### IF MAIN  #####")



            #             # Check if this is the first entry for the combination of date, host, and device_id
            #             if combination not in processed_combinations:
            #                 # Add the combination to the set of processed combinations
            #                 processed_combinations.add(combination)

            #                 # Check if the timestamp is '00:__:__'
            #                 if row[0].split(' ')[1][:3] == '00:':
            #                     # Create a new row with the modified timestamp
            #                     new_row = list(row)
            #                     new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
            #                     new_row[1]=24

            #                     # Insert the new row into the database
            #                     insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
            #                     cursor.execute(insert_query1,new_row)
            #                     print("convert 00:00 to 23:59 as 24",new_row[0],new_row[1],row[3])
                                
            #         elif result is None:
            #             # for row in df.itertuples(index=False):
            #             insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
            #             cursor.execute(insert_query, row)
            #             print('####  ELIF ELIF #####')
            #             # Check if this is the first entry for the combination of date, host, and device_id
            #             if combination not in processed_combinations:
            #                 # Add the combination to the set of processed combinations
            #                 processed_combinations.add(combination)

            #                 # Check if the timestamp is '00:__:__'
            #                 if row[0].split(' ')[1][:3] == '00:':
            #                     # Create a new row with the modified timestamp
            #                     new_row = list(row)
            #                     new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
            #                     new_row[1]=24

            #                     # Insert the new row into the database
            #                     insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
            #                     cursor.execute(insert_query1,new_row)
            #                     print("convert 00:00 to 23:59 as 24",new_row[0],new_row[1],row[3])
            #         connection.commit()
            return True,df
        except mysql.connector.Error as error:
            logging.critical("Error while storing merged files in the database: " + str(error))
            return False, df

    # def store_merged_files_in_db(self, connection, merged_files):
    #     try:
    #         cursor = connection.cursor()
        
    #         df = pd.read_csv(merged_files, header=None)
    #         df = df.fillna(value=0)
        
    #         if df.shape[1] == 9:
    #             df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D', 'wh_1', 'wh_2', 'wh_3']
    #             if pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S', errors='coerce').notnull().all():
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             else:
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
            
    #             unique_dates = df['dt_time'].str[:10].unique()  # Get unique dates
            
    #             for date in unique_dates:
    #                 first_entry = df[df['dt_time'].str.startswith(date)].head(1)
    #                 row = first_entry.iloc[0]
                
    #                 dt_time_date_hour = row.dt_time[:13]
    #                 select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
    #                 cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
    #                 result = cursor.fetchone()
                
    #                 if result is not None:
    #                     delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s" 
    #                     cursor.execute(delete_query, (dt_time_date_hour+"%", row[1], row[2], row[3]))
                    
    #                 insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                 cursor.execute(insert_query, row)
    #                 connection.commit()

    #                 if row[0].split(' ')[1][:3] == '00:':
    #                     new_row = list(row)
    #                     new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                     new_row[1] = 24
    #                     insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                     cursor.execute(insert_query1, new_row)
    #                     connection.commit()
        
    #         if df.shape[1] == 6:
    #             df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D']
    #             if pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S', errors='coerce').notnull().all():
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             else:
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
            
    #             unique_dates = df['dt_time'].str[:10].unique()  # Get unique dates
            
    #             for date in unique_dates:
    #                 first_entry = df[df['dt_time'].str.startswith(date)].head(1)
    #                 row = first_entry.iloc[0]
                
    #                 dt_time_date_hour = row.dt_time[:13]
    #                 select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
    #                 cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
    #                 result = cursor.fetchone()
                
    #                 if result is not None:
    #                     delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s" 
    #                     cursor.execute(delete_query, (dt_time_date_hour+"%", row[1], row[2], row[3]))
                    
    #                 insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                 cursor.execute(insert_query, row)
    #                 connection.commit()

    #                 if row[0].split(' ')[1][:3] == '00:':
    #                     new_row = list(row)
    #                     new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                     new_row[1] = 24
    #                     insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                     cursor.execute(insert_query1, new_row)
    #                     connection.commit()
        
    #         return True, df
    
    #     except mysql.connector.Error as error:
    #         logging.critical("Error while storing merged files in the database: " + str(error))
    #         return False, df

    # def store_merged_files_in_db(self, connection, merged_files):
    #     try:        
    #         cursor = connection.cursor()
            
    #         # user = 'root'
    #         # password = 'root%40123'
    #         # host = 'localhost'
    #         # port = 3306
    #         # database = 'new_rcplasto'
    #         # connection_string = f'mysql+mysqlconnector://{user}:{password}@{host}:{port}/{database}'
    #         # Create a connection to the MySQL database
    #         # engine = create_engine(connection_string)
    #         # Create the jnmc_all_kwh table if it doesn't exist
    #         # Base.metadata.create_all(engine)
    #         # Create a session
    #         # Session = sessionmaker(bind=engine)
    #         # session = Session()
    #         # Read the CSV file into a DataFrame
    #         df = pd.read_csv(merged_files, header=None)
    #         # Replace nan values with None
    #         df = df.fillna(value=0)
    #         # print(df)
    #         # exit()
    #         # Set the column names of the DataFrame to match the column names of the database table
    #         if df.shape[1] == 9:
    #             df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D', 'wh_1', 'wh_2', 'wh_3']
    #             #  Convert the dates in the dt_time column to the correct format
    #             # df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             #df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             if pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S', errors='coerce').notnull().all():
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             else:
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             # try:
    #             #     # try to parse the first element of the dt_time column with the given format
    #             #     datetime.strptime(df['dt_time'].iloc[0], '%d-%m-%Y %H:%M:%S')
    #             #     # if parsing is successful, set the condition to True
    #             #     condition = True
    #             # except ValueError:
    #             #     # if parsing fails, set the condition to False
    #             #     condition = False

    #             # if condition:
    #             #     df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             # else:
    #             #     df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
                
                
    #             # Insert or update rows in the jnmc_all_kwh table
    #             #print('#####  9 wala ',df)
    #             # exit() 
    #             # Insert or update rows in the jnmc_all_kwh table
    #             for row in df.itertuples(index=False):
    #                 # Extract the date and hour from row.dt_time
    #                 dt_time_date_hour = row.dt_time[:13]
    #                 dt_time_with=dt_time_date_hour+"%"
    #                 print(dt_time_with)
    #                 # Check if a row with the same dt_time (with the specified date and hour), host, and device_id values already exists in the table
    #                 # existing_row = session.query(JnmcTestKwh).filter(
    #                 select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
    #                 cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
    #                 result = cursor.fetchone()
    #                 # print(result)
    #                 # print(len(result))
    #                 # exit()
                    
    #                 if result is not None:
    #                     # print(result)
    #                     # exit()
                        
    #                         # Update the existing row with the new values from the DataFrame
    #                     # Insert the original row into the database
    #                     delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s" 
    #                     cursor.execute(delete_query, (dt_time_date_hour+"%", row[1], row[2], row[3]))

    #                     insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                     cursor.execute(insert_query,row)
    #                     print("Insert the original row",row[1],row[2],row[3])
    #                     # connection.commit()

    #                     # insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                     # connection_string.execute(insert_query,row)
                        
    #                     # Check if the timestamp is '00:__:__' #Edit Kanak Kaletha
    #                     if row[0].split(' ')[1][:3] == '00:':
    #                         # Create a new row with the modified timestamp   #Edit Kanak Kaletha
    #                         new_row = list(row)
    #                         new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                         new_row[1]=24
    #                         # Insert the new row into the database
    #                         insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                         cursor.execute(insert_query1,new_row)
    #                     # connection.commit()
    #                 elif result is None:
    #                     # for row in df.itertuples(index=False):
    #                     insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                     cursor.execute(insert_query, row)
    #                     print("Insert the new row into the database",row[1],row[2],row[3])
    #                     if row[0].split(' ')[1][:3] == '00:':
    #                             # Create a new row with the modified timestamp   #Edit Kanak Kaletha
    #                             new_row = list(row)
    #                             new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                             new_row[1]=24
    #                             # Insert the new row into the database
    #                             insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D,wh_1,wh_2,wh_3) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
    #                             cursor.execute(insert_query1,new_row)
    #                             print("###### ELSE IF ########")
    #                 connection.commit()
    #         if df.shape[1] == 6:
    #             df.columns = ['dt_time', 'hour', 'host', 'device_id', 'wh_R', 'wh_D']
    #              # Convert the dates in the dt_time column to the correct format
    #             # df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             #df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             if pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S', errors='coerce').notnull().all():
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             else:
    #                 df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             # try:
    #             #     # try to parse the first element of the dt_time column with the given format
    #             #     datetime.strptime(df['dt_time'].iloc[0], '%d-%m-%Y %H:%M:%S')
    #             #     # if parsing is successful, set the condition to True
    #             #     condition = True
    #             # except ValueError:
    #             #     # if parsing fails, set the condition to False
    #             #     condition = False

    #             # if condition:
    #             #     df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M:%S').dt.strftime('%Y-%m-%d %H:%M:%S')
    #             # else:
    #             #     df['dt_time'] = pd.to_datetime(df['dt_time'], format='%d-%m-%Y %H:%M').dt.strftime('%Y-%m-%d %H:%M:%S')

    #             # Insert or update rows in the jnmc_all_kwh table
    #             #print(" 6 wala ",df)
    #             # exit() 
    #             # Insert or update rows in the jnmc_all_kwh table
    #             for row in df.itertuples(index=False):
    #                 # Extract the date and hour from row.dt_time
    #                 dt_time_date_hour = row.dt_time[:13]
    #                 dt_time_with=dt_time_date_hour+"%"
    #                 print(dt_time_with)
    #                 # Check if a row with the same dt_time (with the specified date and hour), host, and device_id values already exists in the table
    #                 # existing_row = session.query(JnmcTestKwh).filter(
    #                 select_query = "SELECT * FROM jnmc_all_kwh WHERE dt_time LIKE %s AND host=%s AND device_id=%s LIMIT 1"
    #                 cursor.execute(select_query, (f'{dt_time_date_hour}%', row.host, row.device_id))
    #                 result = cursor.fetchone()
                    
    #                 if result is not None:
                        
    #                     delete_query = "DELETE FROM jnmc_all_kwh WHERE dt_time LIKE %s AND hour=%s AND host=%s AND device_id=%s" 
    #                     cursor.execute(delete_query, (dt_time_date_hour+"%", row[1], row[2], row[3]))

    #                     insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                     cursor.execute(insert_query,row)
    #                     print("Insert the row for size 6",row[1],row[2],row[3])

    #                     # Check if the timestamp is '00:__:__' #Edit Kanak Kaletha
    #                     if row[0].split(' ')[1][:3] == '00:':
    #                         # Create a new row with the modified timestamp   #Edit Kanak Kaletha
    #                         new_row = list(row)
    #                         new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                         new_row[1]=24
    #                         # Insert the new row into the database
    #                         insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                         cursor.execute(insert_query1,new_row)
    #                 elif result is None:
    #                     # for row in df.itertuples(index=False):
    #                     insert_query = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                     cursor.execute(insert_query, row)
    #                     print('####  ELIF ELIF #####')
    #                     if row[0].split(' ')[1][:3] == '00:':
    #                             # Create a new row with the modified timestamp   #Edit Kanak Kaletha
    #                             new_row = list(row)
    #                             new_row[0] = (pd.to_datetime(row[0]) - pd.Timedelta(days=1)).strftime('%Y-%m-%d 23:59:59').replace('00:', '23:59:')
    #                             new_row[1]=24
    #                             # Insert the new row into the database
    #                             insert_query1 = "REPLACE INTO jnmc_all_kwh (dt_time,hour ,host ,device_id ,wh_R ,wh_D) VALUES (%s,%s,%s,%s,%s,%s)"
    #                             cursor.execute(insert_query1,new_row)
    #                             print("###### ELSE IF ########")
    #                 connection.commit()
    #         return True,df
    #     except mysql.connector.Error as error:
    #         logging.critical("Error while storing merged files in the database: " + str(error))
    #         return False, df
while True:
    try:
        Dont_Repeat_Yourself()
        time.sleep(30)
    except Exception as e:
        logging.exception("An error occurred: " + str(e))
