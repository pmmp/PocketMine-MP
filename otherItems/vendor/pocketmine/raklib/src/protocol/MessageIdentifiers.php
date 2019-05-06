<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace raklib\protocol;


interface MessageIdentifiers{
	//From https://github.com/OculusVR/RakNet/blob/master/Source/MessageIdentifiers.h

	//
	// RESERVED TYPES - DO NOT CHANGE THESE
	// All types from RakPeer
	//
	/// These types are never returned to the user.
	/// Ping from a connected system.  Update timestamps (internal use only)
	public const ID_CONNECTED_PING = 0x00;
	/// Ping from an unconnected system.  Reply but do not update timestamps. (internal use only)
	public const ID_UNCONNECTED_PING = 0x01;
	/// Ping from an unconnected system.  Only reply if we have open connections. Do not update timestamps. (internal use only)
	public const ID_UNCONNECTED_PING_OPEN_CONNECTIONS = 0x02;
	/// Pong from a connected system.  Update timestamps (internal use only)
	public const ID_CONNECTED_PONG = 0x03;
	/// A reliable packet to detect lost connections (internal use only)
	public const ID_DETECT_LOST_CONNECTIONS = 0x04;
	/// C2S: Initial query: Header(1), OfflineMesageID(16), Protocol number(1), Pad(toMTU), sent with no fragment set.
	/// If protocol fails on server, returns ID_INCOMPATIBLE_PROTOCOL_VERSION to client
	public const ID_OPEN_CONNECTION_REQUEST_1 = 0x05;
	/// S2C: Header(1), OfflineMesageID(16), server GUID(8), HasSecurity(1), Cookie(4, if HasSecurity)
	/// , public key (if do security is true), MTU(2). If public key fails on client, returns ID_PUBLIC_KEY_MISMATCH
	public const ID_OPEN_CONNECTION_REPLY_1 = 0x06;
	/// C2S: Header(1), OfflineMesageID(16), Cookie(4, if HasSecurity is true on the server), clientSupportsSecurity(1 bit),
	/// handshakeChallenge (if has security on both server and client), remoteBindingAddress(6), MTU(2), client GUID(8)
	/// Connection slot allocated if cookie is valid, server is not full, GUID and IP not already in use.
	public const ID_OPEN_CONNECTION_REQUEST_2 = 0x07;
	/// S2C: Header(1), OfflineMesageID(16), server GUID(8), mtu(2), doSecurity(1 bit), handshakeAnswer (if do security is true)
	public const ID_OPEN_CONNECTION_REPLY_2 = 0x08;
	/// C2S: Header(1), GUID(8), Timestamp, HasSecurity(1), Proof(32)
	public const ID_CONNECTION_REQUEST = 0x09;
	/// RakPeer - Remote system requires secure connections, pass a public key to RakPeerInterface::Connect()
	public const ID_REMOTE_SYSTEM_REQUIRES_PUBLIC_KEY = 0x0a;
	/// RakPeer - We passed a public key to RakPeerInterface::Connect(), but the other system did not have security turned on
	public const ID_OUR_SYSTEM_REQUIRES_SECURITY = 0x0b;
	/// RakPeer - Wrong public key passed to RakPeerInterface::Connect()
	public const ID_PUBLIC_KEY_MISMATCH = 0x0c;
	/// RakPeer - Same as ID_ADVERTISE_SYSTEM, but intended for internal use rather than being passed to the user.
	/// Second byte indicates type. Used currently for NAT punchthrough for receiver port advertisement. See ID_NAT_ADVERTISE_RECIPIENT_PORT
	public const ID_OUT_OF_BAND_INTERNAL = 0x0d;
	/// If RakPeerInterface::Send() is called where PacketReliability contains _WITH_ACK_RECEIPT, then on a later call to
	/// RakPeerInterface::Receive() you will get ID_SND_RECEIPT_ACKED or ID_SND_RECEIPT_LOSS. The message will be 5 bytes long,
	/// and bytes 1-4 inclusive will contain a number in native order containing a number that identifies this message.
	/// This number will be returned by RakPeerInterface::Send() or RakPeerInterface::SendList(). ID_SND_RECEIPT_ACKED means that
	/// the message arrived
	public const ID_SND_RECEIPT_ACKED = 0x0e;
	/// If RakPeerInterface::Send() is called where PacketReliability contains UNRELIABLE_WITH_ACK_RECEIPT, then on a later call to
	/// RakPeerInterface::Receive() you will get ID_SND_RECEIPT_ACKED or ID_SND_RECEIPT_LOSS. The message will be 5 bytes long,
	/// and bytes 1-4 inclusive will contain a number in native order containing a number that identifies this message. This number
	/// will be returned by RakPeerInterface::Send() or RakPeerInterface::SendList(). ID_SND_RECEIPT_LOSS means that an ack for the
	/// message did not arrive (it may or may not have been delivered, probably not). On disconnect or shutdown, you will not get
	/// ID_SND_RECEIPT_LOSS for unsent messages, you should consider those messages as all lost.
	public const ID_SND_RECEIPT_LOSS = 0x0f;


	//
	// USER TYPES - DO NOT CHANGE THESE
	//

	/// RakPeer - In a client/server environment, our connection request to the server has been accepted.
	public const ID_CONNECTION_REQUEST_ACCEPTED = 0x10;
	/// RakPeer - Sent to the player when a connection request cannot be completed due to inability to connect.
	public const ID_CONNECTION_ATTEMPT_FAILED = 0x11;
	/// RakPeer - Sent a connect request to a system we are currently connected to.
	public const ID_ALREADY_CONNECTED = 0x12;
	/// RakPeer - A remote system has successfully connected.
	public const ID_NEW_INCOMING_CONNECTION = 0x13;
	/// RakPeer - The system we attempted to connect to is not accepting new connections.
	public const ID_NO_FREE_INCOMING_CONNECTIONS = 0x14;
	/// RakPeer - The system specified in Packet::systemAddress has disconnected from us.  For the client, this would mean the
	/// server has shutdown.
	public const ID_DISCONNECTION_NOTIFICATION = 0x15;
	/// RakPeer - Reliable packets cannot be delivered to the system specified in Packet::systemAddress.  The connection to that
	/// system has been closed.
	public const ID_CONNECTION_LOST = 0x16;
	/// RakPeer - We are banned from the system we attempted to connect to.
	public const ID_CONNECTION_BANNED = 0x17;
	/// RakPeer - The remote system is using a password and has refused our connection because we did not set the correct password.
	public const ID_INVALID_PASSWORD = 0x18;
	// RAKNET_PROTOCOL_VERSION in RakNetVersion.h does not match on the remote system what we have on our system
	// This means the two systems cannot communicate.
	// The 2nd byte of the message contains the value of RAKNET_PROTOCOL_VERSION for the remote system
	public const ID_INCOMPATIBLE_PROTOCOL_VERSION = 0x19;
	// Means that this IP address connected recently, and can't connect again as a security measure. See
	/// RakPeer::SetLimitIPConnectionFrequency()
	public const ID_IP_RECENTLY_CONNECTED = 0x1a;
	/// RakPeer - The sizeof(RakNetTime) bytes following this byte represent a value which is automatically modified by the difference
	/// in system times between the sender and the recipient. Requires that you call SetOccasionalPing.
	public const ID_TIMESTAMP = 0x1b;
	/// RakPeer - Pong from an unconnected system.  First byte is ID_UNCONNECTED_PONG, second sizeof(RakNet::TimeMS) bytes is the ping,
	/// following bytes is system specific enumeration data.
	/// Read using bitstreams
	public const ID_UNCONNECTED_PONG = 0x1c;
	/// RakPeer - Inform a remote system of our IP/Port. On the recipient, all data past ID_ADVERTISE_SYSTEM is whatever was passed to
	/// the data parameter
	public const ID_ADVERTISE_SYSTEM = 0x1d;
	// RakPeer - Downloading a large message. Format is ID_DOWNLOAD_PROGRESS (MessageID), partCount (unsigned int),
	///  partTotal (unsigned int),
	/// partLength (unsigned int), first part data (length <= MAX_MTU_SIZE). See the three parameters partCount, partTotal
	///  and partLength in OnFileProgress in FileListTransferCBInterface.h
	public const ID_DOWNLOAD_PROGRESS = 0x1e;

	/// ConnectionGraph2 plugin - In a client/server environment, a client other than ourselves has disconnected gracefully.
	///   Packet::systemAddress is modified to reflect the systemAddress of this client.
	public const ID_REMOTE_DISCONNECTION_NOTIFICATION = 0x1f;
	/// ConnectionGraph2 plugin - In a client/server environment, a client other than ourselves has been forcefully dropped.
	///  Packet::systemAddress is modified to reflect the systemAddress of this client.
	public const ID_REMOTE_CONNECTION_LOST = 0x20;
	/// ConnectionGraph2 plugin: Bytes 1-4 = count. for (count items) contains {SystemAddress, RakNetGUID, 2 byte ping}
	public const ID_REMOTE_NEW_INCOMING_CONNECTION = 0x21;

	/// FileListTransfer plugin - Setup data
	public const ID_FILE_LIST_TRANSFER_HEADER = 0x22;
	/// FileListTransfer plugin - A file
	public const ID_FILE_LIST_TRANSFER_FILE = 0x23;
	// Ack for reference push, to send more of the file
	public const ID_FILE_LIST_REFERENCE_PUSH_ACK = 0x24;

	/// DirectoryDeltaTransfer plugin - Request from a remote system for a download of a directory
	public const ID_DDT_DOWNLOAD_REQUEST = 0x25;

	/// RakNetTransport plugin - Transport provider message, used for remote console
	public const ID_TRANSPORT_STRING = 0x26;

	/// ReplicaManager plugin - Create an object
	public const ID_REPLICA_MANAGER_CONSTRUCTION = 0x27;
	/// ReplicaManager plugin - Changed scope of an object
	public const ID_REPLICA_MANAGER_SCOPE_CHANGE = 0x28;
	/// ReplicaManager plugin - Serialized data of an object
	public const ID_REPLICA_MANAGER_SERIALIZE = 0x29;
	/// ReplicaManager plugin - New connection, about to send all world objects
	public const ID_REPLICA_MANAGER_DOWNLOAD_STARTED = 0x2a;
	/// ReplicaManager plugin - Finished downloading all serialized objects
	public const ID_REPLICA_MANAGER_DOWNLOAD_COMPLETE = 0x2b;

	/// RakVoice plugin - Open a communication channel
	public const ID_RAKVOICE_OPEN_CHANNEL_REQUEST = 0x2c;
	/// RakVoice plugin - Communication channel accepted
	public const ID_RAKVOICE_OPEN_CHANNEL_REPLY = 0x2d;
	/// RakVoice plugin - Close a communication channel
	public const ID_RAKVOICE_CLOSE_CHANNEL = 0x2e;
	/// RakVoice plugin - Voice data
	public const ID_RAKVOICE_DATA = 0x2f;

	/// Autopatcher plugin - Get a list of files that have changed since a certain date
	public const ID_AUTOPATCHER_GET_CHANGELIST_SINCE_DATE = 0x30;
	/// Autopatcher plugin - A list of files to create
	public const ID_AUTOPATCHER_CREATION_LIST = 0x31;
	/// Autopatcher plugin - A list of files to delete
	public const ID_AUTOPATCHER_DELETION_LIST = 0x32;
	/// Autopatcher plugin - A list of files to get patches for
	public const ID_AUTOPATCHER_GET_PATCH = 0x33;
	/// Autopatcher plugin - A list of patches for a list of files
	public const ID_AUTOPATCHER_PATCH_LIST = 0x34;
	/// Autopatcher plugin - Returned to the user: An error from the database repository for the autopatcher.
	public const ID_AUTOPATCHER_REPOSITORY_FATAL_ERROR = 0x35;
	/// Autopatcher plugin - Returned to the user: The server does not allow downloading unmodified game files.
	public const ID_AUTOPATCHER_CANNOT_DOWNLOAD_ORIGINAL_UNMODIFIED_FILES = 0x36;
	/// Autopatcher plugin - Finished getting all files from the autopatcher
	public const ID_AUTOPATCHER_FINISHED_INTERNAL = 0x37;
	public const ID_AUTOPATCHER_FINISHED = 0x38;
	/// Autopatcher plugin - Returned to the user: You must restart the application to finish patching.
	public const ID_AUTOPATCHER_RESTART_APPLICATION = 0x39;

	/// NATPunchthrough plugin: internal
	public const ID_NAT_PUNCHTHROUGH_REQUEST = 0x3a;
	/// NATPunchthrough plugin: internal
	//ID_NAT_GROUP_PUNCHTHROUGH_REQUEST,
	/// NATPunchthrough plugin: internal
	//ID_NAT_GROUP_PUNCHTHROUGH_REPLY,
	/// NATPunchthrough plugin: internal
	public const ID_NAT_CONNECT_AT_TIME = 0x3b;
	/// NATPunchthrough plugin: internal
	public const ID_NAT_GET_MOST_RECENT_PORT = 0x3c;
	/// NATPunchthrough plugin: internal
	public const ID_NAT_CLIENT_READY = 0x3d;
	/// NATPunchthrough plugin: internal
	//ID_NAT_GROUP_PUNCHTHROUGH_FAILURE_NOTIFICATION,

	/// NATPunchthrough plugin: Destination system is not connected to the server. Bytes starting at offset 1 contains the
	///  RakNetGUID destination field of NatPunchthroughClient::OpenNAT().
	public const ID_NAT_TARGET_NOT_CONNECTED = 0x3e;
	/// NATPunchthrough plugin: Destination system is not responding to ID_NAT_GET_MOST_RECENT_PORT. Possibly the plugin is not installed.
	///  Bytes starting at offset 1 contains the RakNetGUID  destination field of NatPunchthroughClient::OpenNAT().
	public const ID_NAT_TARGET_UNRESPONSIVE = 0x3f;
	/// NATPunchthrough plugin: The server lost the connection to the destination system while setting up punchthrough.
	///  Possibly the plugin is not installed. Bytes starting at offset 1 contains the RakNetGUID  destination
	///  field of NatPunchthroughClient::OpenNAT().
	public const ID_NAT_CONNECTION_TO_TARGET_LOST = 0x40;
	/// NATPunchthrough plugin: This punchthrough is already in progress. Possibly the plugin is not installed.
	///  Bytes starting at offset 1 contains the RakNetGUID destination field of NatPunchthroughClient::OpenNAT().
	public const ID_NAT_ALREADY_IN_PROGRESS = 0x41;
	/// NATPunchthrough plugin: This message is generated on the local system, and does not come from the network.
	///  packet::guid contains the destination field of NatPunchthroughClient::OpenNAT(). Byte 1 contains 1 if you are the sender, 0 if not
	public const ID_NAT_PUNCHTHROUGH_FAILED = 0x42;
	/// NATPunchthrough plugin: Punchthrough succeeded. See packet::systemAddress and packet::guid. Byte 1 contains 1 if you are the sender,
	///  0 if not. You can now use RakPeer::Connect() or other calls to communicate with this system.
	public const ID_NAT_PUNCHTHROUGH_SUCCEEDED = 0x43;

	/// ReadyEvent plugin - Set the ready state for a particular system
	/// First 4 bytes after the message contains the id
	public const ID_READY_EVENT_SET = 0x44;
	/// ReadyEvent plugin - Unset the ready state for a particular system
	/// First 4 bytes after the message contains the id
	public const ID_READY_EVENT_UNSET = 0x45;
	/// All systems are in state ID_READY_EVENT_SET
	/// First 4 bytes after the message contains the id
	public const ID_READY_EVENT_ALL_SET = 0x46;
	/// \internal, do not process in your game
	/// ReadyEvent plugin - Request of ready event state - used for pulling data when newly connecting
	public const ID_READY_EVENT_QUERY = 0x47;

	/// Lobby packets. Second byte indicates type.
	public const ID_LOBBY_GENERAL = 0x48;

	// RPC3, RPC4 error
	public const ID_RPC_REMOTE_ERROR = 0x49;
	/// Plugin based replacement for RPC system
	public const ID_RPC_PLUGIN = 0x4a;

	/// FileListTransfer transferring large files in chunks that are read only when needed, to save memory
	public const ID_FILE_LIST_REFERENCE_PUSH = 0x4b;
	/// Force the ready event to all set
	public const ID_READY_EVENT_FORCE_ALL_SET = 0x4c;

	/// Rooms function
	public const ID_ROOMS_EXECUTE_FUNC = 0x4d;
	public const ID_ROOMS_LOGON_STATUS = 0x4e;
	public const ID_ROOMS_HANDLE_CHANGE = 0x4f;

	/// Lobby2 message
	public const ID_LOBBY2_SEND_MESSAGE = 0x50;
	public const ID_LOBBY2_SERVER_ERROR = 0x51;

	/// Informs user of a new host GUID. Packet::Guid contains this new host RakNetGuid. The old host can be read out using BitStream->Read(RakNetGuid) starting on byte 1
	/// This is not returned until connected to a remote system
	/// If the oldHost is UNASSIGNED_RAKNET_GUID, then this is the first time the host has been determined
	public const ID_FCM2_NEW_HOST = 0x52;
	/// \internal For FullyConnectedMesh2 plugin
	public const ID_FCM2_REQUEST_FCMGUID = 0x53;
	/// \internal For FullyConnectedMesh2 plugin
	public const ID_FCM2_RESPOND_CONNECTION_COUNT = 0x54;
	/// \internal For FullyConnectedMesh2 plugin
	public const ID_FCM2_INFORM_FCMGUID = 0x55;
	/// \internal For FullyConnectedMesh2 plugin
	public const ID_FCM2_UPDATE_MIN_TOTAL_CONNECTION_COUNT = 0x56;
	/// A remote system (not necessarily the host) called FullyConnectedMesh2::StartVerifiedJoin() with our system as the client
	/// Use FullyConnectedMesh2::GetVerifiedJoinRequiredProcessingList() to read systems
	/// For each system, attempt NatPunchthroughClient::OpenNAT() and/or RakPeerInterface::Connect()
	/// When this has been done for all systems, the remote system will automatically be informed of the results
	/// \note Only the designated client gets this message
	/// \note You won't get this message if you are already connected to all target systems
	/// \note If you fail to connect to a system, this does not automatically mean you will get ID_FCM2_VERIFIED_JOIN_FAILED as that system may have been shutting down from the host too
	/// \sa FullyConnectedMesh2::StartVerifiedJoin()
	public const ID_FCM2_VERIFIED_JOIN_START = 0x57;
	/// \internal The client has completed processing for all systems designated in ID_FCM2_VERIFIED_JOIN_START
	public const ID_FCM2_VERIFIED_JOIN_CAPABLE = 0x58;
	/// Client failed to connect to a required systems notified via FullyConnectedMesh2::StartVerifiedJoin()
	/// RakPeerInterface::CloseConnection() was automatically called for all systems connected due to ID_FCM2_VERIFIED_JOIN_START
	/// Programmer should inform the player via the UI that they cannot join this session, and to choose a different session
	/// \note Server normally sends us this message, however if connection to the server was lost, message will be returned locally
	/// \note Only the designated client gets this message
	public const ID_FCM2_VERIFIED_JOIN_FAILED = 0x59;
	/// The system that called StartVerifiedJoin() got ID_FCM2_VERIFIED_JOIN_CAPABLE from the client and then called RespondOnVerifiedJoinCapable() with true
	/// AddParticipant() has automatically been called for this system
	/// Use GetVerifiedJoinAcceptedAdditionalData() to read any additional data passed to RespondOnVerifiedJoinCapable()
	/// \note All systems in the mesh get this message
	/// \sa RespondOnVerifiedJoinCapable()
	public const ID_FCM2_VERIFIED_JOIN_ACCEPTED = 0x5a;
	/// The system that called StartVerifiedJoin() got ID_FCM2_VERIFIED_JOIN_CAPABLE from the client and then called RespondOnVerifiedJoinCapable() with false
	/// CloseConnection() has been automatically called for each system connected to since ID_FCM2_VERIFIED_JOIN_START.
	/// The connection is NOT automatically closed to the original host that sent StartVerifiedJoin()
	/// Use GetVerifiedJoinRejectedAdditionalData() to read any additional data passed to RespondOnVerifiedJoinCapable()
	/// \note Only the designated client gets this message
	/// \sa RespondOnVerifiedJoinCapable()
	public const ID_FCM2_VERIFIED_JOIN_REJECTED = 0x5b;

	/// UDP proxy messages. Second byte indicates type.
	public const ID_UDP_PROXY_GENERAL = 0x5c;

	/// SQLite3Plugin - execute
	public const ID_SQLite3_EXEC = 0x5d;
	/// SQLite3Plugin - Remote database is unknown
	public const ID_SQLite3_UNKNOWN_DB = 0x5e;
	/// Events happening with SQLiteClientLoggerPlugin
	public const ID_SQLLITE_LOGGER = 0x5f;

	/// Sent to NatTypeDetectionServer
	public const ID_NAT_TYPE_DETECTION_REQUEST = 0x60;
	/// Sent to NatTypeDetectionClient. Byte 1 contains the type of NAT detected.
	public const ID_NAT_TYPE_DETECTION_RESULT = 0x61;

	/// Used by the router2 plugin
	public const ID_ROUTER_2_INTERNAL = 0x62;
	/// No path is available or can be established to the remote system
	/// Packet::guid contains the endpoint guid that we were trying to reach
	public const ID_ROUTER_2_FORWARDING_NO_PATH = 0x63;
	/// \brief You can now call connect, ping, or other operations to the destination system.
	///
	/// Connect as follows:
	///
	/// RakNet::BitStream bs(packet->data, packet->length, false);
	/// bs.IgnoreBytes(sizeof(MessageID));
	/// RakNetGUID endpointGuid;
	/// bs.Read(endpointGuid);
	/// unsigned short sourceToDestPort;
	/// bs.Read(sourceToDestPort);
	/// char ipAddressString[32];
	/// packet->systemAddress.ToString(false, ipAddressString);
	/// rakPeerInterface->Connect(ipAddressString, sourceToDestPort, 0,0);
	public const ID_ROUTER_2_FORWARDING_ESTABLISHED = 0x64;
	/// The IP address for a forwarded connection has changed
	/// Read endpointGuid and port as per ID_ROUTER_2_FORWARDING_ESTABLISHED
	public const ID_ROUTER_2_REROUTED = 0x65;

	/// \internal Used by the team balancer plugin
	public const ID_TEAM_BALANCER_INTERNAL = 0x66;
	/// Cannot switch to the desired team because it is full. However, if someone on that team leaves, you will
	///  get ID_TEAM_BALANCER_TEAM_ASSIGNED later.
	/// For TeamBalancer: Byte 1 contains the team you requested to join. Following bytes contain NetworkID of which member
	public const ID_TEAM_BALANCER_REQUESTED_TEAM_FULL = 0x67;
	/// Cannot switch to the desired team because all teams are locked. However, if someone on that team leaves,
	///  you will get ID_TEAM_BALANCER_SET_TEAM later.
	/// For TeamBalancer: Byte 1 contains the team you requested to join.
	public const ID_TEAM_BALANCER_REQUESTED_TEAM_LOCKED = 0x68;
	public const ID_TEAM_BALANCER_TEAM_REQUESTED_CANCELLED = 0x69;
	/// Team balancer plugin informing you of your team. Byte 1 contains the team you requested to join. Following bytes contain NetworkID of which member.
	public const ID_TEAM_BALANCER_TEAM_ASSIGNED = 0x6a;

	/// Gamebryo Lightspeed integration
	public const ID_LIGHTSPEED_INTEGRATION = 0x6b;

	/// XBOX integration
	public const ID_XBOX_LOBBY = 0x6c;

	/// The password we used to challenge the other system passed, meaning the other system has called TwoWayAuthentication::AddPassword() with the same password we passed to TwoWayAuthentication::Challenge()
	/// You can read the identifier used to challenge as follows:
	/// RakNet::BitStream bs(packet->data, packet->length, false); bs.IgnoreBytes(sizeof(RakNet::MessageID)); RakNet::RakString password; bs.Read(password);
	public const ID_TWO_WAY_AUTHENTICATION_INCOMING_CHALLENGE_SUCCESS = 0x6d;
	public const ID_TWO_WAY_AUTHENTICATION_OUTGOING_CHALLENGE_SUCCESS = 0x6e;
	/// A remote system sent us a challenge using TwoWayAuthentication::Challenge(), and the challenge failed.
	/// If the other system must pass the challenge to stay connected, you should call RakPeer::CloseConnection() to terminate the connection to the other system.
	public const ID_TWO_WAY_AUTHENTICATION_INCOMING_CHALLENGE_FAILURE = 0x6f;
	/// The other system did not add the password we used to TwoWayAuthentication::AddPassword()
	/// You can read the identifier used to challenge as follows:
	/// RakNet::BitStream bs(packet->data, packet->length, false); bs.IgnoreBytes(sizeof(MessageID)); RakNet::RakString password; bs.Read(password);
	public const ID_TWO_WAY_AUTHENTICATION_OUTGOING_CHALLENGE_FAILURE = 0x70;
	/// The other system did not respond within a timeout threshhold. Either the other system is not running the plugin or the other system was blocking on some operation for a long time.
	/// You can read the identifier used to challenge as follows:
	/// RakNet::BitStream bs(packet->data, packet->length, false); bs.IgnoreBytes(sizeof(MessageID)); RakNet::RakString password; bs.Read(password);
	public const ID_TWO_WAY_AUTHENTICATION_OUTGOING_CHALLENGE_TIMEOUT = 0x71;
	/// \internal
	public const ID_TWO_WAY_AUTHENTICATION_NEGOTIATION = 0x72;

	/// CloudClient / CloudServer
	public const ID_CLOUD_POST_REQUEST = 0x73;
	public const ID_CLOUD_RELEASE_REQUEST = 0x74;
	public const ID_CLOUD_GET_REQUEST = 0x75;
	public const ID_CLOUD_GET_RESPONSE = 0x76;
	public const ID_CLOUD_UNSUBSCRIBE_REQUEST = 0x77;
	public const ID_CLOUD_SERVER_TO_SERVER_COMMAND = 0x78;
	public const ID_CLOUD_SUBSCRIPTION_NOTIFICATION = 0x79;

	// LibVoice
	public const ID_LIB_VOICE = 0x7a;

	public const ID_RELAY_PLUGIN = 0x7b;
	public const ID_NAT_REQUEST_BOUND_ADDRESSES = 0x7c;
	public const ID_NAT_RESPOND_BOUND_ADDRESSES = 0x7d;
	public const ID_FCM2_UPDATE_USER_CONTEXT = 0x7e;
	public const ID_RESERVED_3 = 0x7f;
	public const ID_RESERVED_4 = 0x80;
	public const ID_RESERVED_5 = 0x81;
	public const ID_RESERVED_6 = 0x82;
	public const ID_RESERVED_7 = 0x83;
	public const ID_RESERVED_8 = 0x84;
	public const ID_RESERVED_9 = 0x85;

	// For the user to use.  Start your first enumeration at this value.
	public const ID_USER_PACKET_ENUM = 0x86;
	//-------------------------------------------------------------------------------------------------------------
}
