# iTunesLibMaze
A tool to merge stars/playCount informations from your iPod to your iTunes Library

# Introduction

## What is this project

* Something made during a Coding Night event, on my spare time...
* something that could (really) be improved

## What this project is NOT

* It's quite specific so maybe you will not find it usefull for your situation
* A code reference (it has been done during a coding event, so quite quickly & dirty...)

## Why?

I had before a complete iTunes Library, well constructed, with stars on my best songs, play count to have smart playlist, etc. It was a paradise... Then I decided to reorganize my files (in terms of directories structure, etc.). To simplify usage, I removed all songs from my library then add everything again... The files were ok, but I lost all informations regarding stars and playCount...

After having cried for several weeks, I remembered that my iPod contains all those informations! Even if the file path are not the same, I can retrieve a song using singer, title, etc. to match with the one inside my iPod...

So the idea was to extract stars & playCount from the iPod and set them in my current iTunes Library...

If you have already tried to have a look in the files containing your iTunes Library, there is 2 files:

* **iTunes Library.itl**: the binary version of your iTunes Library. Difficult/impossible to modify because it's a proprietary binary format from Apple.
* **iTunes Library.xml**: the XML version of your iTunes Library. Unfortunately we could not use it as it because it's a read-only file dedicated to 3rd party softwares... Each time you change your Library in iTunes, this file is overwritten... So no way to use it as an input... Or?

## Eureka!

...there is a way to use the XML as the original Library!

* Once you have your library full (but without stars & playCount), backup the **iTunes Library.xml** file...
* Remove all songs from your Library
* Apply the stars & playCount using this (awesome) tool
* IMPORT the result of this tool in iTunes... And that's it! 

# How to run

## Prerequisite

First you will need to extract the XML version of the library inclued in your iPod. For this I used the [CopyTrans](http://http://fr.copytrans.net/) tool. The problem of this tool is that you should extract everything from your device to get the whole list... It could definitely take a while to do this (and I don't ever speak about disk space if your device is big...)

So the idea was to do this in 2 parts:

* 1st one: order by "stars" the list and only extract the file that have at least one star... At the end you obtain the MP3 files (but you probably don't care because you already have them) and the precious **iTunes Library.xml** file. Copy this one into the *data* directory renaming it into **stars.xml**.
* 2nd one: do the same principle for playCount and store the **iTunes Library.xml** file into the *data* directory renaming it into **counters.xml**.

At this moment you obtain two fake iTunes libraries that contains starred musics (stars.xml) and musics you have already listened to at least one time (counters.xml). Obviously, some songs will be present in both lists, but that's ok... We will deal with this...

You will need also your current **iTunes Library.xml** file. Please have a look in [this page](http://support.apple.com/fr-fr/HT1660) to know where it is located regarding your Operating System. Once you have it, just copy it into the *data* directory under the name **everything.xml**

## Run

Once you have uploaded your 3 files (**stars.xml**, **counters.xml**, **everything.xml**), simply run this command:

	./start.sh

And then import the **final.xml** file in your iTunes... That's it!
